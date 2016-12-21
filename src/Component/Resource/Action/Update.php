<?php

namespace Egg\Component\Resource\Action;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Egg\Component\AbstractComponent;
use Egg\Interfaces\ComponentInterface as Component;

class Update extends AbstractComponent
{
    const ACTION = 'update';

    public function __construct(array $settings = [])
    {
        $this->dependencies = [
            \Egg\Component\Http\Request\ContentType::class,
            \Egg\Component\Resource\Resource::class,
        ];

        $this->settings = array_merge([
            'condition' => '[0-9]+',
        ], $settings);
    }

    public function init()
    {
        $this->container['router']->map(
            static::ACTION,
            'PATCH',
            sprintf('/{id:%s}', $this->settings['condition'])
        );
    }

    public function run(Request $request, Response $response, Component $next)
    {
        $route = $request->getAttribute('route');
        if ($route->getName() == static::ACTION) {
            $resource = $request->getAttribute('resource');
            $id = $route->getArgument('id');
            $params = $request->getBody()->getContent();

            $this->container['request'] = $request;
            $this->container['validator'][$resource]->validate(static::ACTION, [$id, $params]);
            $result = $this->container['controller'][$resource]->execute(static::ACTION, [$id, $params]);

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