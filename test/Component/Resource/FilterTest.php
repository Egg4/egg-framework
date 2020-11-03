<?php

namespace Egg\Component\Resource;

use \PHPUnit\Framework\TestCase;
use \Egg\Component\Resource\Filter as FilterComponent;

class FilterTest extends TestCase
{
    public function testShouldReturnFilter()
    {
        $filter = [
            'param1' => 'value1',
            'param2' => 'value2',
            'param3' => 'value3',
        ];
        $router = \Egg\FactoryTest::createRouter();
        $request = \Egg\FactoryTest::createRequest([
            'QUERY_STRING'          => http_build_query(array_merge($filter, ['range' => '0-10'])),
        ]);
        $route = $router->map('select', 'GET', '');
        $request = $request->withAttribute('route', $route);
        $response = \Egg\FactoryTest::createResponse();

        $component = new FilterComponent();
        $next = function($request, $response) use($filter) {
            $this->assertEquals($filter, $request->getAttribute('filter'));
            return $response;
        };
        $component($request, $response, $next->bindTo($this));
    }
}