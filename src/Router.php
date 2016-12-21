<?php

namespace Egg;

use Psr\Http\Message\ServerRequestInterface as Request;

class Router
{
    protected $slimRouter;

    public function __construct()
    {
        $this->slimRouter = new \Slim\Router();
    }

    public function map($name, $method, $pattern)
    {
        $callable = function($request, $response) {
            return $response;
        };

        return $this->slimRouter->map([$method], $pattern, $callable)->setName($name);
    }

    public function pushGroup($pattern)
    {
        return $this->slimRouter->pushGroup($pattern, function() {});
    }

    public function dispatch(Request $request)
    {
        $routeInfo = $this->slimRouter->dispatch($request);

        if ($routeInfo[0] == \FastRoute\Dispatcher::NOT_FOUND) {
            throw new \Egg\Http\Exception(404, new \Egg\Http\Error(array(
                'name'          => 'not_found',
                'description'   => sprintf('Route "%s" not found', (string) $request->getUri()),
            )));
        }

        if ($routeInfo[0] == \FastRoute\Dispatcher::METHOD_NOT_ALLOWED) {
            throw new \Egg\Http\Exception(405, new \Egg\Http\Error(array(
                'name'          => 'method_not_allowed',
                'description'   => sprintf('Method "%s" not allowed', $request->getMethod()),
            )));
        }

        $routeArguments = [];
        foreach ($routeInfo[2] as $k => $v) {
            $routeArguments[$k] = urldecode($v);
        }
        $route = $this->slimRouter->lookupRoute($routeInfo[1]);
        $route->prepare($request, $routeArguments);
        $request = $request->withAttribute('route', $route);

        $routeInfo['request'] = [$request->getMethod(), (string) $request->getUri()];
        $request = $request->withAttribute('routeInfo', $routeInfo);

        return $request;
    }
}