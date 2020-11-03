<?php

namespace Egg\Component\Resource;

use \PHPUnit\Framework\TestCase;
use \Egg\Component\Resource\Sort as SortComponent;

class SortTest extends TestCase
{
    public function testShouldReturnSort()
    {
        $sort = [
            'param1' => 'desc',
            'param2' => 'asc',
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

        $component = new SortComponent();
        $next = function($request, $response) use($sort) {
            $this->assertEquals($sort, $request->getAttribute('sort'));
            return $response;
        };
        $component($request, $response, $next->bindTo($this));
    }

    public function testShouldRaiseExceptionInvalidSort()
    {
        $router = \Egg\FactoryTest::createRouter();
        $request = \Egg\FactoryTest::createRequest([
            'QUERY_STRING'          => http_build_query([
                'sort'      => 'param2',
                'desc'      => 'param1',
                'range'     => '0-10',
            ]),
        ]);
        $route = $router->map('select', 'GET', '');
        $request = $request->withAttribute('route', $route);
        $response = \Egg\FactoryTest::createResponse();

        $component = new SortComponent();

        $this->expectException(\Egg\Http\Exception::class);
        try {
            $component($request, $response);
        }
        catch (\Egg\Http\Exception $exception) {
            $this->assertEquals(400, $exception->getStatus());
            $errors = $exception->getErrors();
            $this->assertEquals('invalid_sort', $errors[0]->getName());
            throw $exception;
        }
    }
}