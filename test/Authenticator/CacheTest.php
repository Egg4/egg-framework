<?php

namespace Egg\Authenticator;

use \Egg\Container;
use \Egg\Authenticator\Cache as CacheAuthenticator;
use \Egg\Cache\Closure as ClosureCache;

class CacheTest extends \Egg\Test
{
    public function testShouldRegister()
    {
        $user = [
            'id' => 1,
            'login' => 'login@email.com',
        ];

        $container = new Container([
            'cache'     => new ClosureCache(function($action, $arguments) use ($user) {
                $this->assertEquals('set', $action);
                $this->assertEquals($user, $arguments[1]);
            }),
        ]);

        $authenticator = new CacheAuthenticator();
        $authenticator->setContainer($container);
        $authenticator->init();
        $this->assertEquals(32, strlen($authenticator->register($user)));
    }

    public function testShouldUnregister()
    {
        $container = new Container([
            'cache'     => new ClosureCache(function($action, $arguments) {
                $this->assertEquals('delete', $action);
                $this->assertEquals('key', $arguments[0]);
            }),
        ]);

        $authenticator = new CacheAuthenticator();
        $authenticator->setContainer($container);
        $authenticator->init();
        $authenticator->unregister('key');
    }

    public function testShouldAuthenticate()
    {
        $user = [
            'id' => 1,
            'login' => 'login@email.com',
        ];

        $container = new Container([
            'cache'     => new ClosureCache(function($action, $arguments) use ($user) {
                $this->assertEquals('get', $action);
                $this->assertEquals('key', $arguments[0]);
                return $user;
            }),
        ]);

        $authenticator = new CacheAuthenticator();
        $authenticator->setContainer($container);
        $authenticator->init();
        $this->assertEquals($user, $authenticator->authenticate('key'));
    }
}