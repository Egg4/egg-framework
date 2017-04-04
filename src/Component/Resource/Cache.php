<?php

namespace Egg\Component\Resource;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Egg\Component\AbstractComponent;
use Egg\Interfaces\ComponentInterface as Component;

class Cache extends AbstractComponent
{
    public function __construct(array $settings = [])
    {
        $this->dependencies = [
            \Egg\Component\Http\Request\ContentType::class,
            \Egg\Component\Http\Route::class,
            \Egg\Component\Resource\Resource::class,
        ];

        $this->settings = array_merge([
            'key'           => 'resource.{resource}.{id}',
            'route.cache'   => ['read'],
            'route.uncache' => ['replace', 'update', 'delete'],
        ], $settings);
    }

    public function run(Request $request, Response $response, Component $next)
    {
        $route = $request->getAttribute('route');
        $routeName = $route->getName();
        $replacements = [
            '{resource}' => $route->getArgument('resource'),
            '{id}' => $route->getArgument('id'),
        ];
        $key = str_replace(
            array_keys($replacements),
            array_values($replacements),
            $this->settings['key']
        );

        if (in_array($routeName, $this->settings['route.cache'])) {
            $content = $this->container['cache']->get($key);
            if ($content) {
                $response->getBody()->setContent($content);
                return $response;
            }
        }
        elseif (in_array($routeName, $this->settings['route.uncache'])) {
            $this->container['cache']->delete($key);
        }

        $response = $next($request, $response);

        if (in_array($routeName, $this->settings['route.cache'])) {
            $content = $response->getBody()->getContent();
            $this->container['cache']->set($key, $content);
        }

        return $response;
    }
}