<?php

namespace Egg\Authenticator;

use \Egg\Authenticator\Cache as CacheAuthenticator;
use \Egg\Cache\Memory as MemoryCache;

class CacheTest extends \Egg\Test
{
    protected static $authenticator;

    public static function setUpBeforeClass()
    {
        static::$authenticator = new CacheAuthenticator([
            'cache' => new MemoryCache(),
        ]);
    }

    public function testSuccess()
    {
        $user = [
            'id' => 1,
            'login' => 'login@email.com',
        ];

        $key = static::$authenticator->create($user);
        $this->assertEquals(32, strlen($key));
        $this->assertEquals($user, static::$authenticator->get($key));
    }

    public function testFailure()
    {
        $key = 'fake_key';

        $this->assertEquals(false, static::$authenticator->get($key));
    }
}