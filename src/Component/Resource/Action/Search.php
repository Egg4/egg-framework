<?php

namespace Egg\Component\Resource\Action;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Egg\Component\AbstractComponent;
use Egg\Interfaces\ComponentInterface as Component;

class Search extends AbstractComponent
{
    const ACTION = 'search';

    public function __construct(array $settings = [])
    {
        $this->dependencies = [
            \Egg\Component\Http\Request\ContentType::class,
            \Egg\Component\Resource\Resource::class,
            \Egg\Component\Resource\Filter::class,
            \Egg\Component\Resource\Sort::class,
            \Egg\Component\Resource\Range::class,
        ];

        $this->settings = array_merge([
            'searchKey' => 'search',
        ], $settings);
    }

    public function init()
    {
        $this->container['router']->map(
            static::ACTION,
            'GET',
            sprintf('/%s', $this->settings['searchKey'])
        );
    }

    public function run(Request $request, Response $response, Component $next)
    {
        $route = $request->getAttribute('route');
        if ($route->getName() == static::ACTION) {
            $resource = $request->getAttribute('resource');
            $filterParams = $request->getAttribute('filter');
            $sortParams = $request->getAttribute('sort');
            $rangeParams = $request->getAttribute('range');

            $this->container['request'] = $request;
            $this->container['response'] = $response;
            $filterParams = $this->container['authorizer'][$resource]->authorize(static::ACTION, [$filterParams, $sortParams, $rangeParams]);
            $this->container['validator'][$resource]->validate(static::ACTION, [$filterParams, $sortParams, $rangeParams]);
            $result = $this->container['controller'][$resource]->execute(static::ACTION, [$filterParams, $sortParams, $rangeParams]);
            $content = $this->container['serializer'][$resource]->serialize($result);

            $response = $response->withStatus(200);
            $response->getBody()->setContent($content);
        }

        $response = $next($request, $response);

        return $response;
    }
}