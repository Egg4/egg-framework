<?php

namespace Egg\Component\Resource;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Egg\Component\AbstractComponent;
use Egg\Interfaces\ComponentInterface as Component;

class Version extends AbstractComponent
{
    public function __construct(array $settings = [])
    {
        $this->dependencies = [
            \Egg\Component\Http\Route::class,
        ];

        $this->settings = array_merge([
            'condition' => '[a-z0-9\.\-]+',
        ], $settings);
    }

    public function init()
    {
        $pattern = sprintf('/{version:%s}', $this->settings['condition']);
        $this->container['router']->pushGroup($pattern);
    }

    public function run(Request $request, Response $response, Component $next)
    {
        $route = $request->getAttribute('route');
        $request = $request->withAttribute('version', $route->getArgument('version'));

        $response = $next($request, $response);

        return $response;
    }
}