<?php

namespace Egg\Component\Resource\Action;

use \PHPUnit\Framework\TestCase;
use \Egg\Container;
use \Egg\Component\Resource\Action\Custom as CustomComponent;
use \Egg\Authorizer\Closure as ClosureAuthorizer;
use \Egg\Validator\Closure as ClosureValidator;
use \Egg\Controller\Closure as ClosureController;

class CustomTest extends TestCase
{
    public function testShouldReturn200()
    {
        $body = [
            'param1' => 'value1',
            'param2' => 'value2',
        ];
        $result = 27;

        $authorizer = new ClosureAuthorizer(function($action, $arguments) use($body) {
            $this->assertEquals($action, 'sendEmail');
            $this->assertEquals($arguments[0], $body);
        });

        $validator = new ClosureValidator(function($action, $arguments) use($body) {
            $this->assertEquals($action, 'sendEmail');
            $this->assertEquals($arguments[0], $body);
        });

        $controller = new ClosureController(function($action, $arguments) use($body, $result) {
            $this->assertEquals($action, 'sendEmail');
            $this->assertEquals($arguments[0], $body);
            return $result;
        });

        $container = new Container([
            'router'        => \Egg\FactoryTest::createRouter(),
            'authorizer'    => new Container(['users' => $authorizer]),
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
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($result, $response->getBody()->getContent());
    }

    public function testShouldReturn204()
    {
        $body = [
            'param1' => 'value1',
            'param2' => 'value2',
        ];
        $result = [];

        $authorizer = new ClosureAuthorizer(function($action, $arguments) use($body) {
            $this->assertEquals($action, 'sendEmail');
            $this->assertEquals($arguments[0], $body);
        });

        $validator = new ClosureValidator(function($action, $arguments) use($body) {
            $this->assertEquals($action, 'sendEmail');
            $this->assertEquals($arguments[0], $body);
        });

        $controller = new ClosureController(function($action, $arguments) use($body, $result) {
            $this->assertEquals($action, 'sendEmail');
            $this->assertEquals($arguments[0], $body);
            return $result;
        });

        $container = new Container([
            'router'        => \Egg\FactoryTest::createRouter(),
            'authorizer'    => new Container(['users' => $authorizer]),
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