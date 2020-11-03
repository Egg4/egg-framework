<?php

namespace Egg\Component\Resource\Action;

use \PHPUnit\Framework\TestCase;
use \Egg\Container;
use \Egg\Component\Resource\Action\Delete as DeleteComponent;
use \Egg\Authorizer\Closure as ClosureAuthorizer;
use \Egg\Validator\Closure as ClosureValidator;
use \Egg\Controller\Closure as ClosureController;
use \Egg\Serializer\Closure as ClosureSerializer;

class DeleteTest extends TestCase
{
    public function testShouldReturn200()
    {
        $result = new \StdClass();
        $result->id = 27;

        $authorizer = new ClosureAuthorizer(function($action, $arguments) use($result) {
            $this->assertEquals($action, 'delete');
            $this->assertEquals($arguments[0], $result->id);
        });

        $validator = new ClosureValidator(function($action, $arguments) use($result) {
            $this->assertEquals($action, 'delete');
            $this->assertEquals($arguments[0], $result->id);
        });

        $controller = new ClosureController(function($action, $arguments) use($result) {
            $this->assertEquals($action, 'delete');
            $this->assertEquals($arguments[0], $result->id);
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
            'REQUEST_METHOD'        => 'DELETE',
            'REQUEST_URI'           => sprintf('/%s', $result->id),
        ]);
        $request = $request->withAttribute('resource', 'users');
        $response = \Egg\FactoryTest::createResponse();

        $component = new DeleteComponent();
        $component->setContainer($container);
        $component->init();
        $request = $container['router']->dispatch($request);

        $response = $component($request, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('result', $response->getBody()->getContent());
    }
}