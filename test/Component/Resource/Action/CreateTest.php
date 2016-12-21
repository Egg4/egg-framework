<?php

namespace Egg\Component\Resource\Action;

use \Egg\Container;
use \Egg\Component\Resource\Action\Create as CreateComponent;
use \Egg\Validator\Closure as ClosureValidator;
use \Egg\Controller\Closure as ClosureController;
use \Egg\Serializer\Closure as ClosureSerializer;

class CreateTest extends \Egg\Test
{
    public function testShouldReturn201()
    {
        $body = [
            'param1' => 'value1',
            'param2' => 'value2',
        ];
        $result = new \StdClass();
        $result->id = 27;

        $controller = new ClosureController(function($action, $arguments) use($body, $result) {
            $this->assertEquals($action, 'create');
            $this->assertEquals($arguments[0], $body);
            return $result;
        });

        $validator = new ClosureValidator(function($action, $arguments) use($body) {
            $this->assertEquals($action, 'create');
            $this->assertEquals($arguments[0], $body);
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
            'REQUEST_METHOD'        => 'POST',
            'REQUEST_URI'           => '',
        ], [], $body);
        $request = $request->withAttribute('resource', 'users');
        $response = \Egg\FactoryTest::createResponse();

        $component = new CreateComponent();
        $component->setContainer($container);
        $component->init();
        $request = $container['router']->dispatch($request);

        $response = $component($request, $response);
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertContains((string) $request->getUri() . '/' . $result->id, $response->getHeaderLine('Location'));
        $this->assertEquals('result', $response->getBody()->getContent());
    }
}