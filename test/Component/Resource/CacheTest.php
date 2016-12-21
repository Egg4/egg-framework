<?php

namespace Egg\Component\Resource;

use \Egg\Container;
use \Egg\Component\Resource\Cache as CacheComponent;
use \Egg\Cache\Closure as CacheClosure;

class CacheTest extends \Egg\Test
{
    public function testShouldWriteCache()
    {
        $container = new Container([
            'router' => \Egg\FactoryTest::createRouter(),
            'cache'  => new CacheClosure(function($action, $arguments) {
                static $i = 0;
                if ($i == 0) {
                    $this->assertEquals('get', $action);
                    $this->assertEquals('key', $arguments[0]);
                }
                else {
                    $this->assertEquals('set', $action);
                    $this->assertEquals('key', $arguments[0]);
                    $this->assertEquals('data', $arguments[1]);
                }
                $i++;
            }),
        ]);
        $request = \Egg\FactoryTest::createRequest();
        $route = $container['router']->map('read', 'GET', '');
        $request = $request->withAttribute('route', $route);
        $response = \Egg\FactoryTest::createResponse();

        $component = new CacheComponent(['keyPattern' => 'key']);
        $component->setContainer($container);
        $response = $component($request, $response, function($request, $response) {
            $response->getBody()->setContent('data');
            return $response;
        });
        $this->assertEquals('data', $response->getBody()->getContent());
    }

    public function testShouldReadCache()
    {
        $container = new Container([
            'router' => \Egg\FactoryTest::createRouter(),
            'cache'  => new CacheClosure(function($action, $arguments) {
                $this->assertEquals('get', $action);
                return 'data';
            }),
        ]);
        $request = \Egg\FactoryTest::createRequest();
        $route = $container['router']->map('read', 'GET', '');
        $request = $request->withAttribute('route', $route);
        $response = \Egg\FactoryTest::createResponse();

        $component = new CacheComponent();
        $component->setContainer($container);
        $next = function() {
            $this->fail();
        };
        $component($request, $response, $next->bindTo($this));
        $this->assertEquals('data', $response->getBody()->getContent());
    }

    public function testShouldDeleteCache()
    {
        $container = new Container([
            'router' => \Egg\FactoryTest::createRouter(),
            'cache'  => new CacheClosure(function($action, $arguments) {
                $this->assertEquals('delete', $action);
            }),
        ]);
        $request = \Egg\FactoryTest::createRequest();
        $route = $container['router']->map('update', 'PATCH', '');
        $request = $request->withAttribute('route', $route);
        $response = \Egg\FactoryTest::createResponse();

        $component = new CacheComponent();
        $component->setContainer($container);
        $response = $component($request, $response, function($request, $response) {
            $response->getBody()->setContent('data');
            return $response;
        });
        $this->assertEquals('data', $response->getBody()->getContent());
    }
}