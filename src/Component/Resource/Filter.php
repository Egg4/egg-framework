<?php

namespace Egg\Component\Resource;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Egg\Component\AbstractComponent;
use Egg\Interfaces\ComponentInterface as Component;

class Filter extends AbstractComponent
{
    public function __construct(array $settings = [])
    {
        $this->dependencies = [
            \Egg\Component\Http\Route::class,
        ];

        $this->settings = array_merge([
            'routes'        => ['select', 'search'],
            'skipParams'    => ['range', 'sort', 'asc', 'desc'],
        ], $settings);
    }

    public function run(Request $request, Response $response, Component $next)
    {
        $route = $request->getAttribute('route');
        if (in_array($route->getName(), $this->settings['routes'])) {
            $filterParams = [];
            $params = $request->getQueryParams();
            foreach ($params as $key => $value) {
                if (in_array($key, $this->settings['skipParams'])) continue;
                if (strtolower($value) == 'null')   $value = null;
                if (strpos($value, ',') !== false)  $value = explode(',', $value);
                $filterParams[$key] = $value;
            }
            $request = $request->withAttribute('filter', $filterParams);
        }

        $response = $next($request, $response);

        return $response;
    }
}