<?php

namespace Egg\Component\Http;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Egg\Component\AbstractComponent;
use Egg\Interfaces\ComponentInterface as Component;

class Route extends AbstractComponent
{
    public function __construct(array $settings = [])
    {
        $this->dependencies = [
            \Egg\Component\Http\Exception::class,
        ];

        $this->settings = array_merge([

        ], $settings);
    }

    public function run(Request $request, Response $response, Component $next)
    {
        $request = $this->container['router']->dispatch($request);
        $response = $next($request, $response);

        return $response;
    }
}