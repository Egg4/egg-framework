<?php

namespace Egg;

use Psr\Http\Message\ServerRequestInterface as Request;

class Router extends \Slim\Router
{
    public function map($name, $method, $pattern)
    {
        $callable = function($request, $response) {
            return $response;
        };

        return parent::map([$method], $pattern, $callable)->setName($name);
    }

    public function pushGroup($pattern, $callable = null)
    {
        return parent::pushGroup($pattern, function() {});
    }

    public function dispatch(Request $request)
    {
        $routeInfo = parent::dispatch($request);

        if ($routeInfo[0] == \FastRoute\Dispatcher::NOT_FOUND) {
            throw new \Exception('not_found');
        }

        if ($routeInfo[0] == \FastRoute\Dispatcher::METHOD_NOT_ALLOWED) {
            throw new \Exception('not_allowed');
        }

        $routeArguments = [];
        foreach ($routeInfo[2] as $k => $v) {
            $routeArguments[$k] = urldecode($v);
        }
        $route = parent::lookupRoute($routeInfo[1]);
        $route->prepare($request, $routeArguments);
        $request = $request->withAttribute('route', $route);

        $routeInfo['request'] = [$request->getMethod(), (string) $request->getUri()];
        $request = $request->withAttribute('routeInfo', $routeInfo);

        return $request;
    }
}