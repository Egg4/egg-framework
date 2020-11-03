<?php

namespace Egg\Component\Http;

use \PHPUnit\Framework\TestCase;
use \Egg\Container;
use \Egg\Component\Http\Route as RouteComponent;

class RouteTest extends TestCase
{
    public function testShouldDispatchToSelectRoute()
    {
        $request = \Egg\FactoryTest::createRequest([
            'REQUEST_METHOD'        => 'GET',
            'REQUEST_URI'           => '/v1.0/users',
        ]);
        $response = \Egg\FactoryTest::createResponse();
        $container = new Container([
            'router' => \Egg\FactoryTest::createRouter(),
        ]);

        $component = new RouteComponent();
        $component->setContainer($container);
        $container['router']->map('select', 'GET', '/{version}/{resource}');
        $next = function($request, $response) {
            $route = $request->getAttribute('route');
            $this->assertEquals($route->getName(), 'select');
            $arguments = $route->getArguments();
            $this->assertEquals($arguments['version'], 'v1.0');
            $this->assertEquals($arguments['resource'], 'users');
            return $response;
        };
        $component($request, $response, $next->bindTo($this));
    }

    public function testShouldThrowExceptionWithNotFound()
    {
        $request = \Egg\FactoryTest::createRequest([
            'REQUEST_METHOD'        => 'GET',
            'REQUEST_URI'           => '/12/v1.0/users',
        ]);
        $response = \Egg\FactoryTest::createResponse();
        $container = new Container([
            'router' => \Egg\FactoryTest::createRouter(),
        ]);

        $component = new RouteComponent();
        $component->setContainer($container);
        $container['router']->map('select', 'GET', '/{version}/{resource}');

        $this->expectException(\Egg\Http\Exception::class);
        try {
            $component($request, $response);
        }
        catch (\Egg\Http\Exception $exception) {
            $this->assertEquals(404, $exception->getStatus());
            $errors = $exception->getErrors();
            $this->assertEquals('not_found', $errors[0]->getName());
            throw $exception;
        }
    }

    public function testShouldThrowExceptionWithMethodNotAllowed()
    {
        $request = \Egg\FactoryTest::createRequest([
            'REQUEST_METHOD'        => 'PUT',
            'REQUEST_URI'           => '/v1.0/users',
        ]);
        $response = \Egg\FactoryTest::createResponse();
        $container = new Container([
            'router' => \Egg\FactoryTest::createRouter(),
        ]);

        $component = new RouteComponent();
        $component->setContainer($container);
        $container['router']->map('select', 'GET', '/{version}/{resource}');

        $this->expectException(\Egg\Http\Exception::class);
        try {
            $component($request, $response);
        }
        catch (\Egg\Http\Exception $exception) {
            $this->assertEquals(405, $exception->getStatus());
            $errors = $exception->getErrors();
            $this->assertEquals('method_not_allowed', $errors[0]->getName());
            throw $exception;
        }
    }
}