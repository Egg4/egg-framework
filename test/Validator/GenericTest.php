<?php

namespace Egg\Validator;

use \Egg\Container;
use \Egg\Validator\Generic as GenericValidator;
use \Egg\Orm\Repository\Closure as ClosureRepository;
use \Egg\Orm\Schema\File as FileSchema;
use \Egg\Parser\Json as JsonParser;

class GenericTest extends \Egg\Test
{
    protected static $container;

    public static function setUpBeforeClass()
    {
        static::$container = new \Egg\Container([
            'request'   => \Egg\FactoryTest::createRequest(),
            'response'  => \Egg\FactoryTest::createResponse(),
        ]);

        static::$container['schema'] = new FileSchema([
            'cache'             => new \Egg\Cache\Memory(),
            'filename'          => __DIR__ . '/schema.json',
            'parser'            => new JsonParser(),
        ]);
    }

    public function testReadShouldRaiseExceptionNotFound()
    {
        $id = 27;

        static::$container['repository'] = new Container([
            'user'     => new ClosureRepository(function($action, $arguments) use ($id) {
                $this->assertEquals('selectOne', $action);
                $this->assertEquals(['id' => $id], $arguments[0]);
                return null;
            }),
        ]);

        $validator = new GenericValidator([
            'container' => static::$container,
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
            $this->assertContains('not found', $errors[0]->getDescription());
            throw $exception;
        }
    }

    public function testCreateShouldRaiseExceptionInvalidContentParamRequired()
    {
        $validator = new GenericValidator([
            'container' => static::$container,
            'resource'  => 'house',
        ]);

        $this->expectException(\Egg\Http\Exception::class);
        try {
            $validator->validate('create', [[]]);
        }
        catch (\Egg\Http\Exception $exception) {
            $this->assertEquals(400, $exception->getStatus());
            $errors = $exception->getErrors();
            $this->assertEquals('invalid_content', $errors[0]->getName());
            $this->assertContains('is required', $errors[0]->getDescription());
            throw $exception;
        }
    }

    public function testCreateShouldRaiseExceptionInvalidContentParamNotNullable()
    {
        $validator = new GenericValidator([
            'container' => static::$container,
            'resource'  => 'house',
        ]);

        $this->expectException(\Egg\Http\Exception::class);
        try {
            $validator->validate('create', [[
                'user_id'   => null,
                'name'      => 'test',
            ]]);
        }
        catch (\Egg\Http\Exception $exception) {
            $this->assertEquals(400, $exception->getStatus());
            $errors = $exception->getErrors();
            $this->assertEquals('invalid_content', $errors[0]->getName());
            $this->assertContains('is null', $errors[0]->getDescription());
            throw $exception;
        }
    }

    public function testCreateShouldRaiseExceptionInvalidContentParamIntegerExpected()
    {
        $validator = new GenericValidator([
            'container' => static::$container,
            'resource'  => 'house',
        ]);

        $this->expectException(\Egg\Http\Exception::class);
        try {
            $validator->validate('create', [[
                'user_id'   => 'test',
                'name'      => 'test',
            ]]);
        }
        catch (\Egg\Http\Exception $exception) {
            $this->assertEquals(400, $exception->getStatus());
            $errors = $exception->getErrors();
            $this->assertEquals('invalid_content', $errors[0]->getName());
            $this->assertContains('integer expected', $errors[0]->getDescription());
            throw $exception;
        }
    }

    public function testCreateShouldRaiseExceptionInvalidContentParamUnsignedExpected()
    {
        $validator = new GenericValidator([
            'container' => static::$container,
            'resource'  => 'house',
        ]);

        $this->expectException(\Egg\Http\Exception::class);
        try {
            $validator->validate('create', [[
                'user_id'   => '-12',
                'name'      => 'test',
            ]]);
        }
        catch (\Egg\Http\Exception $exception) {
            $this->assertEquals(400, $exception->getStatus());
            $errors = $exception->getErrors();
            $this->assertEquals('invalid_content', $errors[0]->getName());
            $this->assertContains('unsigned expected', $errors[0]->getDescription());
            throw $exception;
        }
    }

    public function testCreateShouldRaiseExceptionInvalidContentParamMaxLengthExpected()
    {
        static::$container['repository'] = new Container([
            'user'     => new ClosureRepository(function($action, $arguments) {
                $this->assertEquals('selectOne', $action);
                $this->assertEquals(['id' => 1], $arguments[0]);
                return (object) ['id' => 1, 'login' => 'test'];
            }),
        ]);

        $validator = new GenericValidator([
            'container' => static::$container,
            'resource'  => 'house',
        ]);

        $this->expectException(\Egg\Http\Exception::class);
        try {
            $validator->validate('create', [[
                'user_id'   => 1,
                'name'      => 'very_long_house_name',
            ]]);
        }
        catch (\Egg\Http\Exception $exception) {
            $this->assertEquals(400, $exception->getStatus());
            $errors = $exception->getErrors();
            $this->assertEquals('invalid_content', $errors[0]->getName());
            $this->assertContains('max length', $errors[0]->getDescription());
            throw $exception;
        }
    }

    public function testCreateShouldRaiseExceptionForeignEntityNotFound()
    {
        static::$container['repository'] = new Container([
            'user'     => new ClosureRepository(function($action, $arguments) {
                $this->assertEquals('selectOne', $action);
                $this->assertEquals(['id' => 1], $arguments[0]);
                return null;
            }),
        ]);

        $validator = new GenericValidator([
            'container' => static::$container,
            'resource'  => 'house',
        ]);

        $this->expectException(\Egg\Http\Exception::class);
        try {
            $validator->validate('create', [[
                'user_id'   => 1,
                'name'      => 'test',
            ]]);
        }
        catch (\Egg\Http\Exception $exception) {
            $this->assertEquals(400, $exception->getStatus());
            $errors = $exception->getErrors();
            $this->assertEquals('not_found', $errors[0]->getName());
            $this->assertContains('not found', $errors[0]->getDescription());
            throw $exception;
        }
    }

    public function testCreateShouldRaiseExceptionNotUnique()
    {
        static::$container['repository'] = new Container([
            'user'     => new ClosureRepository(function($action, $arguments) {
                $this->assertEquals('selectOne', $action);
                $this->assertEquals(['login' => 'login1'], $arguments[0]);
                return (object) ['id' => 1, 'login' => 'login1'];
            }),
        ]);

        $validator = new GenericValidator([
            'container' => static::$container,
            'resource'  => 'user',
        ]);

        $this->expectException(\Egg\Http\Exception::class);
        try {
            $validator->validate('create', [[
                'login'   => 'login1',
            ]]);
        }
        catch (\Egg\Http\Exception $exception) {
            $this->assertEquals(400, $exception->getStatus());
            $errors = $exception->getErrors();
            $this->assertEquals('not_unique', $errors[0]->getName());
            $this->assertContains('not unique', $errors[0]->getDescription());
            throw $exception;
        }
    }
}