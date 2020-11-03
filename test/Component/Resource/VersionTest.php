<?php

namespace Egg\Component\Resource;

use \PHPUnit\Framework\TestCase;
use \Egg\Container;
use \Egg\Component\Resource\Version as VersionComponent;

class VersionTest extends TestCase
{
    public function testShouldReturnVersion()
    {
        $container = new Container([
            'router' => \Egg\FactoryTest::createRouter(),
        ]);
        $request = \Egg\FactoryTest::createRequest([
            'REQUEST_METHOD'        => 'GET',
            'REQUEST_URI'           => '/v1.0/users',
        ]);
        $response = \Egg\FactoryTest::createResponse();

        $component = new VersionComponent();
        $component->setContainer($container);
        $component->init();
        $container['router']->map('read', 'GET', '/users');
        $request = $container['router']->dispatch($request);
        $next = function($request, $response) use ($container) {
            $this->assertEquals('v1.0', $request->getAttribute('version'));
            return $response;
        };
        $component($request, $response, $next->bindTo($this));
    }
}