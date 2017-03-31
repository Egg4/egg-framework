<?php

namespace Egg\Component\Resource\Action;

use \Egg\Container;
use \Egg\Component\Resource\Action\Search as SearchComponent;
use \Egg\Authorizer\Closure as ClosureAuthorizer;
use \Egg\Validator\Closure as ClosureValidator;
use \Egg\Controller\Closure as ClosureController;
use \Egg\Serializer\Closure as ClosureSerializer;

class SearchTest extends \Egg\Test
{
    public function testShouldReturn200()
    {
        $filter = [
            'param1' => 'value1',
            'param2' => 'value2',
        ];
        $sort = [
            'param1' => 'desc',
            'param2' => 'asc',
        ];
        $range = [
            'offset'    => 0,
            'limit'     => 11,
        ];
        $result = 27;

        $authorizer = new ClosureAuthorizer(function($action, $arguments) use($filter, $sort, $range, $result) {
            $this->assertEquals($action, 'search');
            $this->assertEquals($arguments[0], $filter);
            $this->assertEquals($arguments[1], $sort);
            $this->assertEquals($arguments[2], $range);
            return $filter;
        });

        $validator = new ClosureValidator(function($action, $arguments) use($filter, $sort, $range) {
            $this->assertEquals($action, 'search');
            $this->assertEquals($arguments[0], $filter);
            $this->assertEquals($arguments[1], $sort);
            $this->assertEquals($arguments[2], $range);
        });

        $controller = new ClosureController(function($action, $arguments) use($filter, $sort, $range, $result) {
            $this->assertEquals($action, 'search');
            $this->assertEquals($arguments[0], $filter);
            $this->assertEquals($arguments[1], $sort);
            $this->assertEquals($arguments[2], $range);
            return $result;
        });

        $serializer = new ClosureSerializer(function($input) use($result) {
            $this->assertEquals($input, $result);
            return 'result';
        });

        $container = new Container([
            'router'        => \Egg\FactoryTest::createRouter(),
            'authorizer'    => new Container(['users' => $authorizer]),
            'validator'     => new Container(['users' => $validator]),
            'controller'    => new Container(['users' => $controller]),
            'serializer'    => new Container(['users' => $serializer]),
        ]);
        $request = \Egg\FactoryTest::createRequest([
            'REQUEST_METHOD'        => 'GET',
            'REQUEST_URI'           => '/search',
        ]);
        $request = $request->withAttribute('resource', 'users');
        $request = $request->withAttribute('filter', $filter);
        $request = $request->withAttribute('sort', $sort);
        $request = $request->withAttribute('range', $range);
        $response = \Egg\FactoryTest::createResponse();

        $component = new SearchComponent();
        $component->setContainer($container);
        $component->init();
        $request = $container['router']->dispatch($request);

        $response = $component($request, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('result', $response->getBody()->getContent());
    }
}