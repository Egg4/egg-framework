<?php

namespace Egg\Component\Resource\Action;

use \Egg\Container;
use \Egg\Component\Resource\Action\Update as UpdateComponent;
use \Egg\Validator\Closure as ClosureValidator;
use \Egg\Controller\Closure as ClosureController;
use \Egg\Serializer\Closure as ClosureSerializer;

class UpdateTest extends \Egg\Test
{
    public function testShouldReturn200()
    {
        $body = [
            'param1' => 'value1',
            'param2' => 'value2',
        ];
        $result = new \StdClass();
        $result->id = 27;

        $controller = new ClosureController(function($action, $arguments) use($body, $result) {
            $this->assertEquals($action, 'update');
            $this->assertEquals($arguments[0], $result->id);
            $this->assertEquals($arguments[1], $body);
            return $result;
        });

        $validator = new ClosureValidator(function($action, $arguments) use($body, $result) {
            $this->assertEquals($action, 'update');
            $this->assertEquals($arguments[0], $result->id);
            $this->assertEquals($arguments[1], $body);
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
            'REQUEST_METHOD'        => 'PATCH',
            'REQUEST_URI'           => sprintf('/%s', $result->id),
        ], [], $body);
        $request = $request->withAttribute('resource', 'users');
        $response = \Egg\FactoryTest::createResponse();

        $component = new UpdateComponent();
        $component->setContainer($container);
        $component->init();
        $request = $container['router']->dispatch($request);

        $response = $component($request, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('result', $response->getBody()->getContent());
    }
}