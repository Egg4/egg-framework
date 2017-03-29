<?php

namespace Egg\Component\Resource\Action;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Egg\Component\AbstractComponent;
use Egg\Interfaces\ComponentInterface as Component;

class Create extends AbstractComponent
{
    const ACTION = 'create';

    public function __construct(array $settings = [])
    {
        $this->dependencies = [
            \Egg\Component\Http\Request\ContentType::class,
            \Egg\Component\Resource\Resource::class,
        ];

        $this->settings = array_merge([

        ], $settings);
    }

    public function init()
    {
        $this->container['router']->map(static::ACTION, 'POST', '/');
        $this->container['router']->map(static::ACTION, 'POST', '');
    }

    public function run(Request $request, Response $response, Component $next)
    {
        $route = $request->getAttribute('route');
        if ($route->getName() == static::ACTION) {
            $resource = $request->getAttribute('resource');
            $params = $request->getBody()->getContent();

            $this->container['request'] = $request;
            $this->container['response'] = $response;
            $this->container['authorizer'][$resource]->authorize(static::ACTION, [$params]);
            $this->container['validator'][$resource]->validate(static::ACTION, [$params]);
            $result = $this->container['controller'][$resource]->execute(static::ACTION, [$params]);
            $content = $this->container['serializer'][$resource]->serialize($result);

            $response = $response->withStatus(201);
            if (isset($result->id)) {
                $location = (string) $request->getUri() . '/' . $result->id;
                $response = $response->withHeader('Location', $location);
            }
            $response->getBody()->setContent($content);
        }

        $response = $next($request, $response);

        return $response;
    }
}