<?php

namespace Egg\Component\Resource;

use \PHPUnit\Framework\TestCase;
use \Egg\Container;
use \Egg\Component\Resource\Resource as ResourceComponent;

class ResourceTest extends TestCase
{
    public function testShouldReturnResource()
    {
        $container = new Container([
            'router' => \Egg\FactoryTest::createRouter(),
        ]);
        $request = \Egg\FactoryTest::createRequest([
            'REQUEST_METHOD'        => 'GET',
            'REQUEST_URI'           => '/users',
        ]);
        $response = \Egg\FactoryTest::createResponse();

        $component = new ResourceComponent();
        $component->setContainer($container);
        $component->init();
        $container['router']->map('read', 'GET', '');
        $request = $container['router']->dispatch($request);
        $next = function($request, $response) use ($container) {
            $this->assertEquals('users', $request->getAttribute('resource'));
            return $response;
        };
        $component($request, $response, $next->bindTo($this));
    }
}