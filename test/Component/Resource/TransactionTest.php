<?php

namespace Egg\Component\Resource;

use \Egg\Container;
use \Egg\Component\Resource\Transaction as TransactionComponent;
use \Egg\Orm\Database\Closure as ClosureDatabase;

class TransactionTest extends \Egg\Test
{
    public function testShouldCommitTransaction()
    {
        $container = new Container([
            'router'    => \Egg\FactoryTest::createRouter(),
            'database'  => new ClosureDatabase(function($action) {
                static $i = 0;
                if ($i == 0) $this->assertEquals('beginTransaction', $action);
                else $this->assertEquals('commit', $action);
                $i++;
            }),
        ]);
        $request = \Egg\FactoryTest::createRequest();
        $route = $container['router']->map('create', 'POST', '');
        $request = $request->withAttribute('route', $route);
        $response = \Egg\FactoryTest::createResponse();

        $component = new TransactionComponent();
        $component->setContainer($container);
        $component($request, $response);
    }

    public function testShouldRollbackTransaction()
    {
        $container = new Container([
            'router'    => \Egg\FactoryTest::createRouter(),
            'database'  => new ClosureDatabase(function($action) {
                static $i = 0;
                if ($i == 0) $this->assertEquals('beginTransaction', $action);
                else $this->assertEquals('rollback', $action);
                $i++;
            }),
        ]);
        $request = \Egg\FactoryTest::createRequest();
        $route = $container['router']->map('create', 'POST', '');
        $request = $request->withAttribute('route', $route);
        $response = \Egg\FactoryTest::createResponse();

        $component = new TransactionComponent();
        $component->setContainer($container);

        $this->expectException(\Exception::class);
        $component($request, $response, function() {
            throw new \Exception();
        });
    }

    public function testShouldSkipTransaction()
    {
        $container = new Container([
            'router'    => \Egg\FactoryTest::createRouter(),
            'database'  => new ClosureDatabase(function() {
                $this->fail();
            }),
        ]);
        $request = \Egg\FactoryTest::createRequest();
        $route = $container['router']->map('read', 'GET', '');
        $request = $request->withAttribute('route', $route);
        $response = \Egg\FactoryTest::createResponse();

        $component = new TransactionComponent();
        $component->setContainer($container);
        $component($request, $response);
    }
}