<?php

namespace Egg\Component\Resource;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Egg\Component\AbstractComponent;
use Egg\Interfaces\ComponentInterface as Component;

class Sort extends AbstractComponent
{
    public function __construct(array $settings = [])
    {
        $this->dependencies = [
            \Egg\Component\Http\Route::class,
        ];

        $this->settings = array_merge([
            'routes'    => ['select', 'search'],
            'key.sort'   => 'sort',
            'key.desc'   => 'desc',
        ], $settings);
    }

    public function run(Request $request, Response $response, Component $next)
    {
        $route = $request->getAttribute('route');
        if (in_array($route->getName(), $this->settings['routes'])) {
            $sort = $request->getQueryParam($this->settings['key.sort'], '');
            $sort = empty($sort) ? [] : explode(',', $sort);
            $desc = $request->getQueryParam($this->settings['key.desc'], '');
            $desc = empty($desc) ? [] : explode(',', $desc);

            // check params
            $errorParams = [];
            foreach ($desc as $param) {
                if (!in_array($param, $sort)) {
                    $errorParams[] = $param;
                }
            }
            if (!empty($errorParams)) {
                throw new \Egg\Http\Exception($response, 400, new \Egg\Http\Error(array(
                    'name'          => 'invalid_sort',
                    'description'   => sprintf('Sort key "%s" must contain "%s" keys',
                        $this->settings['key.sort'],
                        implode(',', $errorParams)
                    ),
                )));
            }

            $sortParams = [];
            foreach ($sort as $param) {
                $sortParams[$param] = in_array($param, $desc) ? 'desc' : 'asc';
            }
            $request = $request->withAttribute('sort', $sortParams);
        }

        $response = $next($request, $response);

        return $response;
    }
}