<?php

namespace Egg\Component\Resource\Action;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Egg\Component\AbstractComponent;
use Egg\Interfaces\ComponentInterface as Component;

class Custom extends AbstractComponent
{
    const ACTION = 'custom';

    public function __construct(array $settings = [])
    {
        $this->dependencies = [
            \Egg\Component\Http\Request\ContentType::class,
            \Egg\Component\Resource\Resource::class,
        ];

        $this->settings = array_merge([
            'condition' => '[a-z][a-z0-9_]*',
        ], $settings);
    }

    public function init()
    {
        $this->container['router']->map(
            static::ACTION,
            'POST',
            sprintf('/{custom:%s}', $this->settings['condition'])
        );
    }

    public function run(Request $request, Response $response, Component $next)
    {
        $route = $request->getAttribute('route');
        if ($route->getName() == static::ACTION) {
            $resource = $request->getAttribute('resource');
            $custom = $route->getArgument(static::ACTION);
            $action = \Egg\Yolk\String::camelize($custom);
            $params = $request->getBody()->getContent();

            $this->container['request'] = $request;
            $this->container['response'] = $response;
            $this->container['authorizer'][$resource]->authorize($action, [$params]);
            $this->container['validator'][$resource]->validate($action, [$params]);
            $result = $this->container['controller'][$resource]->execute($action, [$params]);

            if (empty($result)) {
                $response = $response->withStatus(204);
            }
            else {
                $content = $this->container['serializer'][$resource]->serialize($result);
                $response = $response->withStatus(200);
                $response->getBody()->setContent($content);
            }
        }

        $response = $next($request, $response);

        return $response;
    }
}