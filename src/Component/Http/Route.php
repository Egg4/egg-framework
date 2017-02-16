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
        try {
            $request = $this->container['router']->dispatch($request);
            $response = $next($request, $response);
        }
        catch (\Exception $exception) {
            if ($exception->getMessage() == 'not_found') {
                throw new \Egg\Http\Exception($response, 404, new \Egg\Http\Error(array(
                    'name'          => 'not_found',
                    'description'   => sprintf('Route "%s" not found', (string) $request->getUri()),
                )));
            }
            if ($exception->getMessage() == 'not_allowed') {
                throw new \Egg\Http\Exception($response, 405, new \Egg\Http\Error(array(
                    'name'          => 'method_not_allowed',
                    'description'   => sprintf('Method "%s" not allowed', $request->getMethod()),
                )));
            }
            throw $exception;
        }

        return $response;
    }
}