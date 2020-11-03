<?php

namespace Egg\Controller;

use \PHPUnit\Framework\TestCase;
use \Egg\Container;
use \Egg\Controller\Generic as GenericController;
use \Egg\Orm\Repository\Closure as ClosureRepository;

class GenericTest extends TestCase
{
    public function testShouldCreateResource()
    {
        $id = 27;
        $data = [
            'param1' => 'value1',
            'param2' => 'value2',
        ];

        $request = \Egg\FactoryTest::createRequest();

        $container = new Container([
            'request'   => $request,
            'repository' => new Container([
                'user'     => new ClosureRepository(function($action, $arguments) use($id, $data) {
                    static $i = 0;
                    $i++;
                    if ($i == 1) {
                        $this->assertEquals('insert', $action);
                        $this->assertEquals($data, $arguments[0]);
                        return $id;
                    }
                    else {
                        $this->assertEquals('selectOne', $action);
                        $this->assertEquals(['id' => $id], $arguments[0]);
                        return $data;
                    }
                }),
            ]),
        ]);

        $controller = new GenericController([
            'container' => $container,
            'resource'  => 'user',
        ]);

        $result = $controller->execute('create', [$data]);
        $this->assertEquals($data, $result);
    }

    public function testShouldReadResource()
    {
        $id = 27;
        $data = [
            'param1' => 'value1',
            'param2' => 'value2',
        ];

        $request = \Egg\FactoryTest::createRequest();

        $container = new Container([
            'request'   => $request,
            'repository' => new Container([
                'user' => new ClosureRepository(function($action, $arguments) use($id, $data) {
                    $this->assertEquals('selectOne', $action);
                    $this->assertEquals(['id' => $id], $arguments[0]);
                    return $data;
                }),
            ]),
        ]);

        $controller = new GenericController([
            'container' => $container,
            'resource'  => 'user',
        ]);

        $result = $controller->execute('read', [$id]);
        $this->assertEquals($data, $result);
    }

    public function testShouldUpdateResource()
    {
        $id = 27;
        $data = [
            'param1' => 'value1',
            'param2' => 'value2',
        ];

        $request = \Egg\FactoryTest::createRequest();

        $container = new Container([
            'request'   => $request,
            'repository' => new Container([
                'user' => new ClosureRepository(function($action, $arguments) use($id, $data) {
                    static $i = 0;
                    $i++;
                    if ($i == 1) {
                        $this->assertEquals('update', $action);
                        $this->assertEquals($data, $arguments[0]);
                        $this->assertEquals(['id' => $id], $arguments[1]);
                        return 1;
                    }
                    else {
                        $this->assertEquals('selectOne', $action);
                        $this->assertEquals(['id' => $id], $arguments[0]);
                        return $data;
                    }
                }),
            ]),
        ]);

        $controller = new GenericController([
            'container' => $container,
            'resource'  => 'user',
        ]);

        $result = $controller->execute('update', [$id, $data]);
        $this->assertEquals($data, $result);
    }

    public function testShouldDeleteResource()
    {
        $id = 27;

        $request = \Egg\FactoryTest::createRequest();

        $container = new Container([
            'request'   => $request,
            'repository' => new Container([
                'user' => new ClosureRepository(function($action, $arguments) use($id) {
                    $this->assertEquals('delete', $action);
                    $this->assertEquals(['id' => $id], $arguments[0]);
                }),
            ]),
        ]);

        $controller = new GenericController([
            'container' => $container,
            'resource'  => 'user',
        ]);

        $controller->execute('delete', [$id]);
    }
}