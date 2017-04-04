<?php

namespace Egg\Validator;

use \Egg\Container;
use \Egg\Validator\Generic as GenericValidator;
use \Egg\Orm\Repository\Closure as ClosureRepository;

class GenericTest extends \Egg\Test
{
    public function testCreateShouldRaiseExceptionInvalidContent()
    {
        $data = [];

        $request = \Egg\FactoryTest::createRequest();
        $response = \Egg\FactoryTest::createResponse();

        $container = new Container([
            'request'   => $request,
            'response'  => $response,
            'repository' => new Container([
                'user'     => new ClosureRepository(function($action, $arguments) {

                }),
            ]),
        ]);

        $validator = new GenericValidator([
            'container' => $container,
            'resource'  => 'user',
        ]);

        $this->expectException(\Egg\Http\Exception::class);
        try {
            $validator->validate('create', [$data]);
        }
        catch (\Egg\Http\Exception $exception) {
            $this->assertEquals(400, $exception->getStatus());
            $errors = $exception->getErrors();
            $this->assertEquals('invalid_content', $errors[0]->getName());
            throw $exception;
        }
    }

    public function testReadShouldRaiseExceptionNotFound()
    {
        $id = 27;

        $request = \Egg\FactoryTest::createRequest();
        $response = \Egg\FactoryTest::createResponse();

        $container = new Container([
            'request'   => $request,
            'response'  => $response,
            'repository' => new Container([
                'user'     => new ClosureRepository(function($action, $arguments) use ($id) {
                    $this->assertEquals('selectOne', $action);
                    $this->assertEquals(['id' => $id], $arguments[0]);
                    return null;
                }),
            ]),
        ]);

        $validator = new GenericValidator([
            'container' => $container,
            'resource'  => 'user',
        ]);

        $this->expectException(\Egg\Http\Exception::class);
        try {
            $validator->validate('read', [$id]);
        }
        catch (\Egg\Http\Exception $exception) {
            $this->assertEquals(400, $exception->getStatus());
            $errors = $exception->getErrors();
            $this->assertEquals('not_found', $errors[0]->getName());
            throw $exception;
        }
    }
}