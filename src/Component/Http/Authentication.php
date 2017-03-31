<?php

namespace Egg\Component\Http;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Egg\Component\AbstractComponent;
use Egg\Interfaces\ComponentInterface as Component;

class Authentication extends AbstractComponent
{
    public function __construct(array $settings = [])
    {
        $this->dependencies = [
            \Egg\Component\Http\Route::class,
        ];

        $this->settings = array_merge([
            'header.key'        => 'Authorization',
            'header.pattern'    => '{key}',
            'routes'            => [],
        ], $settings);
    }

    public function run(Request $request, Response $response, Component $next)
    {
        $route = $request->getAttribute('route');

        if (!$this->isPublic($route)) {
            $authentication = null;
            $headerLine = $request->getHeaderLine($this->settings['header.key']);
            $tokens = $this->parseHeaderLine($headerLine);
            if (!empty($tokens)) {
                $key = reset($tokens); // get the first key
                $authentication = $this->container['authenticator']->authenticate($key);
            }
            if (!$authentication) {
                throw new \Egg\Http\Exception($response, 403, new \Egg\Http\Error(array(
                    'name'          => 'authentication_required',
                    'description'   => 'Authentication is required',
                )));
            }
            $request = $request->withAttribute('authentication', $authentication);
        }

        $response = $next($request, $response);

        return $response;
    }

    protected function parseHeaderLine($headerLine)
    {
        $pattern = $this->buildPregPattern($this->settings['header.pattern']);
        preg_match($pattern, $headerLine, $matches);
        if (count($matches) == 0) return [];
        foreach ($matches as $key => $value) {
            if (is_numeric($key)) unset($matches[$key]);
        }
        return $matches;
    }

    protected function buildPregPattern($pattern)
    {
        preg_match_all('/{([^}]*)}/', $pattern, $matches);
        if (count($matches) != 2) return '//';

        $replacements = [];
        foreach ($matches[0] as $i => $key) {
            $replacements[$key] = sprintf('(?P<%s>\w+)', $matches[1][$i]);
        }

        return sprintf('/%s/', str_replace(
            array_keys($replacements),
            array_values($replacements),
            $pattern
        ));
    }

    protected function isPublic($route)
    {
        foreach ($this->settings['routes'] as $key => $value) {
            if ($value != 'public') {
                continue;
            }
            list($resource, $action) = explode('.', $key);
            if (!in_array($resource, ['*', $route->getArgument('resource')])) {
                continue;
            }
            if (!in_array($action, ['*', $route->getName(), $route->getArgument('action')])) {
                continue;
            }
            return true;
        }

        return false;
    }
}