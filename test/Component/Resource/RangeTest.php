<?php

namespace Egg\Component\Resource;

use \PHPUnit\Framework\TestCase;
use \Egg\Component\Resource\Range as RangeComponent;

class RangeTest extends TestCase
{
    public function testShouldReturnSort()
    {
        $range = [
            'offset'    => 0,
            'limit'     => 11,
        ];
        $router = \Egg\FactoryTest::createRouter();
        $request = \Egg\FactoryTest::createRequest([
            'QUERY_STRING'          => http_build_query([
                'sort'      => 'param1,param2',
                'desc'      => 'param1',
                'range'     => '0-10',
            ]),
        ]);
        $route = $router->map('select', 'GET', '');
        $request = $request->withAttribute('route', $route);
        $response = \Egg\FactoryTest::createResponse();

        $component = new RangeComponent();
        $next = function($request, $response) use($range) {
            $this->assertEquals($range, $request->getAttribute('range'));
            return $response;
        };
        $component($request, $response, $next->bindTo($this));
    }

    public function testShouldRaiseExceptionUnparsableRange()
    {
        $router = \Egg\FactoryTest::createRouter();
        $request = \Egg\FactoryTest::createRequest([
            'QUERY_STRING'          => http_build_query([
                'range'     => 'unparsable',
            ]),
        ]);
        $route = $router->map('select', 'GET', '');
        $request = $request->withAttribute('route', $route);
        $response = \Egg\FactoryTest::createResponse();

        $component = new RangeComponent();

        $this->expectException(\Egg\Http\Exception::class);
        try {
            $component($request, $response);
        }
        catch (\Egg\Http\Exception $exception) {
            $this->assertEquals(400, $exception->getStatus());
            $errors = $exception->getErrors();
            $this->assertEquals('unparsable_range', $errors[0]->getName());
            throw $exception;
        }
    }

    public function testShouldRaiseExceptionInvalidRange()
    {
        $router = \Egg\FactoryTest::createRouter();
        $request = \Egg\FactoryTest::createRequest([
            'QUERY_STRING'          => http_build_query([
                'range'     => '10-9',
            ]),
        ]);
        $route = $router->map('select', 'GET', '');
        $request = $request->withAttribute('route', $route);
        $response = \Egg\FactoryTest::createResponse();

        $component = new RangeComponent();

        $this->expectException(\Egg\Http\Exception::class);
        try {
            $component($request, $response);
        }
        catch (\Egg\Http\Exception $exception) {
            $this->assertEquals(400, $exception->getStatus());
            $errors = $exception->getErrors();
            $this->assertEquals('invalid_range', $errors[0]->getName());
            throw $exception;
        }
    }
}