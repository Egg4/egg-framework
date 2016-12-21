<?php

namespace Egg\Component\Resource;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Egg\Component\AbstractComponent;
use Egg\Interfaces\ComponentInterface as Component;

class Transaction extends AbstractComponent
{
    public function __construct(array $settings = [])
    {
        $this->dependencies = [
            \Egg\Component\Http\Route::class,
        ];

        $this->settings = array_merge([
            'routes'    => ['create', 'replace', 'update', 'delete'],
        ], $settings);
    }

    public function run(Request $request, Response $response, Component $next)
    {
        $route = $request->getAttribute('route');
        if (in_array($route->getName(), $this->settings['routes'])) {
            try {
                $this->container['database']->beginTransaction();
                $response = $next($request, $response);
                $this->container['database']->commit();
            }
            catch (\Exception $exception) {
                $this->container['database']->rollback();
                throw $exception;
            }
        }
        else {
            $response = $next($request, $response);
        }

        return $response;
    }
}