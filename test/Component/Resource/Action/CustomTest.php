<?php

namespace Egg\Component\Resource\Action;

use \Egg\Container;
use \Egg\Component\Resource\Action\Custom as CustomComponent;
use \Egg\Validator\Closure as ClosureValidator;
use \Egg\Controller\Closure as ClosureController;
use \Egg\Serializer\Closure as ClosureSerializer;

class CustomTest extends \Egg\Test
{
    public function testShouldReturn200()
    {
        $body = [
            'param1' => 'value1',
            'param2' => 'value2',
        ];
        $result = 27;

        $controller = new ClosureController(function($action, $arguments) use($body, $result) {
            $this->assertEquals($action, 'sendEmail');
            $this->assertEquals($arguments[0], $body);
            return $result;
        });

        $validator = new ClosureValidator(function($action, $arguments) use($body) {
            $this->assertEquals($action, 'sendEmail');
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
            'REQUEST_URI'           => '/send_email',
        ], [], $body);
        $request = $request->withAttribute('resource', 'users');
        $response = \Egg\FactoryTest::createResponse();

        $component = new CustomComponent();
        $component->setContainer($container);
        $component->init();
        $request = $container['router']->dispatch($request);

        $response = $component($request, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('result', $response->getBody()->getContent());
    }

    public function testShouldReturn204()
    {
        $body = [
            'param1' => 'value1',
            'param2' => 'value2',
        ];
        $result = [];

        $controller = new ClosureController(function($action, $arguments) use($body, $result) {
            $this->assertEquals($action, 'sendEmail');
            $this->assertEquals($arguments[0], $body);
            return $result;
        });

        $validator = new ClosureValidator(function($action, $arguments) use($body) {
            $this->assertEquals($action, 'sendEmail');
            $this->assertEquals($arguments[0], $body);
        });

        $container = new Container([
            'router'        => \Egg\FactoryTest::createRouter(),
            'controller'    => new Container(['users' => $controller]),
            'validator'     => new Container(['users' => $validator]),
        ]);
        $request = \Egg\FactoryTest::createRequest([
            'REQUEST_METHOD'        => 'POST',
            'REQUEST_URI'           => '/send_email',
        ], [], $body);
        $request = $request->withAttribute('resource', 'users');
        $response = \Egg\FactoryTest::createResponse();

        $component = new CustomComponent();
        $component->setContainer($container);
        $component->init();
        $request = $container['router']->dispatch($request);

        $response = $component($request, $response);
        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEquals(null, $response->getBody()->getContent());
    }
}