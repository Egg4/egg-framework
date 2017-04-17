<?php

namespace Egg\Authenticator;

use \Egg\Container;
use \Egg\Authenticator\Cache as CacheAuthenticator;
use \Egg\Cache\Closure as ClosureCache;

class CacheTest extends \Egg\Test
{
    public function testShouldCreate()
    {
        $user = [
            'id' => 1,
            'login' => 'login@email.com',
        ];

        $container = new Container([
            'cache'     => new ClosureCache(function($action, $arguments) use ($user) {
                $this->assertEquals('set', $action);
                $this->assertEquals($user['login'], $arguments[1]['login']);
            }),
        ]);

        $authenticator = new CacheAuthenticator([
            'container' => $container,
        ]);

        $authentication = $authenticator->create($user);
        $this->assertEquals(32, strlen($authentication['key']));
    }

    public function testShouldDelete()
    {
        $container = new Container([
            'cache'     => new ClosureCache(function($action, $arguments) {
                $this->assertEquals('delete', $action);
                $this->assertEquals('authentication.key', $arguments[0]);
            }),
        ]);

        $authenticator = new CacheAuthenticator([
            'container' => $container,
            'namespace' => 'authentication',
        ]);

        $authenticator->delete('key');
    }

    public function testShouldGet()
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

        $authenticator = new CacheAuthenticator([
            'container' => $container,
            'namespace' => '',
        ]);

        $authentication = $authenticator->get('key');

        $this->assertEquals($user['login'], $authentication['login']);
    }
}