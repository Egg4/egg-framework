<?php

namespace Egg\Component\Resource;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Egg\Component\AbstractComponent;
use Egg\Interfaces\ComponentInterface as Component;

class Mutex extends AbstractComponent
{
    public function __construct(array $settings = [])
    {
        $this->dependencies = [
            \Egg\Component\Resource\Resource::class,
            \Egg\Component\Http\Authentication::class,
        ];

        $this->settings = array_merge([
            'key'       => 'mutex.{resource}.{id}',
            'ttl'       => 600,
            'routes'    => ['replace', 'update', 'delete'],
            'condition' => '[0-9]+',
        ], $settings);
    }

    public function init()
    {
        $this->container['router']->map(
            'lock',
            'POST',
            sprintf('/{id:%s}/lock', $this->settings['condition'])
        );
        $this->container['router']->map(
            'unlock',
            'POST',
            sprintf('/{id:%s}/unlock', $this->settings['condition'])
        );
    }

    public function run(Request $request, Response $response, Component $next)
    {
        $this->container['request'] = $request;
        $this->container['response'] = $response;
        $route = $request->getAttribute('route');

        if ($route->getName() == 'lock') {
            $this->lockResource($route);
        }
        elseif ($route->getName() == 'unlock') {
            $this->unlockResource($route);
        }
        elseif (in_array($route->getName(), $this->settings['routes'])) {
            $this->accessResource($route);
        }

        $response = $next($request, $response);

        if (in_array($route->getName(), $this->settings['routes'])) {
            $this->freeResource($route);
        }

        return $response;
    }

    protected function buildKey($route)
    {
        $replacements = [
            '{resource}' => $route->getArgument('resource'),
            '{id}' => $route->getArgument('id'),
        ];
        return str_replace(
            array_keys($replacements),
            array_values($replacements),
            $this->settings['key']
        );
    }

    protected function lockResource($route)
    {
        $this->container['authorizer'][$route->getArgument('resource')]->authorize('lock');
        $key = $this->buildKey($route);
        $authenticationKey = $this->container['cache']->get($key);
        $authentication = $this->container['request']->getAttribute('authentication');
        if ($authenticationKey AND $authentication['key'] != $authenticationKey) {
            throw new \Egg\Http\Exception($this->container['response'], 409, new \Egg\Http\Error(array(
                'name' => 'lock_failure',
                'description' => sprintf(
                    'Resource "%s (%s)" is locked',
                    $route->getArgument('resource'),
                    $route->getArgument('id')
                ),
            )));
        }
        $this->container['cache']->set($key, $authentication['key'], $this->settings['ttl']);
    }

    protected function unlockResource($route)
    {
        $this->container['authorizer'][$route->getArgument('resource')]->authorize('lock');
        $key = $this->buildKey($route);
        $authenticationKey = $this->container['cache']->get($key);
        if ($authenticationKey) {
            $authentication = $this->container['request']->getAttribute('authentication');
            if ($authentication['key'] != $authenticationKey) {
                throw new \Egg\Http\Exception($this->container['response'], 409, new \Egg\Http\Error(array(
                    'name' => 'unlock_failure',
                    'description' => sprintf(
                        'Resource "%s (%s)" is locked by someone else',
                        $route->getArgument('resource'),
                        $route->getArgument('id')
                    ),
                )));
            }
            $this->container['cache']->delete($key);
        }
    }

    protected function accessResource($route)
    {
        $key = $this->buildKey($route);
        $authenticationKey = $this->container['cache']->get($key);
        if ($authenticationKey) {
            $authentication = $this->container['request']->getAttribute('authentication');
            if ($authentication['key'] != $authenticationKey) {
                throw new \Egg\Http\Exception($this->container['response'], 409, new \Egg\Http\Error(array(
                    'name' => 'access_failure',
                    'description' => sprintf(
                        'Resource "%s (%s)" is locked by someone else',
                        $route->getArgument('resource'),
                        $route->getArgument('id')
                    ),
                )));
            }
        }
        else {
            throw new \Egg\Http\Exception($this->container['response'], 409, new \Egg\Http\Error(array(
                'name' => 'not_locked_failure',
                'description' => sprintf(
                    'Resource "%s (%s)" should be locked before access',
                    $route->getArgument('resource'),
                    $route->getArgument('id')
                ),
            )));
        }
    }

    protected function freeResource($route)
    {
        $key = $this->buildKey($route);
        $this->container['cache']->delete($key);
    }
}