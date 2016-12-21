<?php

namespace Egg\Component\Resource\Action;

use \Egg\Container;
use \Egg\Component\Resource\Action\Read as ReadComponent;
use \Egg\Validator\Closure as ClosureValidator;
use \Egg\Controller\Closure as ClosureController;
use \Egg\Serializer\Closure as ClosureSerializer;

class ReadTest extends \Egg\Test
{
    public function testShouldReturn200()
    {
        $result = new \StdClass();
        $result->id = 27;

        $controller = new ClosureController(function($action, $arguments) use($result) {
            $this->assertEquals($action, 'read');
            $this->assertEquals($arguments[0], $result->id);
            return $result;
        });

        $validator = new ClosureValidator(function($action, $arguments) use($result) {
            $this->assertEquals($action, 'read');
            $this->assertEquals($arguments[0], $result->id);
        });

        $serializer = new ClosureSerializer(function($input) use($result) {
            $this->assertEquals($input, $result);
            return 'result';
        });

        $container = new Container([
            'router'        => \Egg\FactoryTest::createRouter(),
            'controller'    => new Container(['users' => $controller]),
            'validator'     => new Container(['users' => $validator]),
            'serializer'    => new Container(['users' => $serializer]),
        ]);
        $request = \Egg\FactoryTest::createRequest([
            'REQUEST_METHOD'        => 'GET',
            'REQUEST_URI'           => sprintf('/%s', $result->id),
        ]);
        $request = $request->withAttribute('resource', 'users');
        $response = \Egg\FactoryTest::createResponse();

        $component = new ReadComponent();
        $component->setContainer($container);
        $component->init();
        $request = $container['router']->dispatch($request);

        $response = $component($request, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('result', $response->getBody()->getContent());
    }
}