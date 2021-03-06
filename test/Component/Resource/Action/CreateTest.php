<?php

namespace Egg\Component\Resource\Action;

use \PHPUnit\Framework\TestCase;
use \Egg\Container;
use \Egg\Component\Resource\Action\Create as CreateComponent;
use \Egg\Authorizer\Closure as ClosureAuthorizer;
use \Egg\Validator\Closure as ClosureValidator;
use \Egg\Controller\Closure as ClosureController;
use \Egg\Serializer\Closure as ClosureSerializer;

class CreateTest extends TestCase
{
    public function testShouldReturn201()
    {
        $body = [
            'param1' => 'value1',
            'param2' => 'value2',
        ];
        $result = new \StdClass();
        $result->id = 27;

        $authorizer = new ClosureAuthorizer(function($action, $arguments) use($body) {
            $this->assertEquals($action, 'create');
            $this->assertEquals($arguments[0], $body);
        });

        $validator = new ClosureValidator(function($action, $arguments) use($body) {
            $this->assertEquals($action, 'create');
            $this->assertEquals($arguments[0], $body);
        });

        $controller = new ClosureController(function($action, $arguments) use($body, $result) {
            $this->assertEquals($action, 'create');
            $this->assertEquals($arguments[0], $body);
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
        $this->assertStringContainsString((string) $request->getUri() . '/' . $result->id, $response->getHeaderLine('Location'));
        $this->assertEquals('result', $response->getBody()->getContent());
    }
}