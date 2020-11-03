<?php

namespace Egg\Authenticator;

use \PHPUnit\Framework\TestCase;
use \Egg\Authenticator\Token as TokenAuthenticator;

class TokenTest extends TestCase
{
    protected static $authenticator;

    public static function setUpBeforeClass(): void
    {
        static::$authenticator = new TokenAuthenticator([
            'secret' => 'sfG684sqHJsdf54sf6ds4F56ds4f64Et',
        ]);
    }

    public function testSuccess()
    {
        $user = [
            'id' => 1,
            'login' => 'login@email.com',
        ];

        $key = static::$authenticator->create($user);
        $this->assertEquals($user, static::$authenticator->get($key));
    }

    public function testFakeKey()
    {
        $key = 'fake_key';

        $this->assertEquals(false, static::$authenticator->get($key));
    }

    public function testExpired()
    {
        $user = [
            'id' => 1,
            'login' => 'login@email.com',
        ];

        $authenticator = new TokenAuthenticator([
            'secret'    => 'sfG684sqHJsdf54sf6ds4F56ds4f64Et',
            'timeout'   => -1,
        ]);

        $key = $authenticator->create($user);
        $this->assertEquals(false, $authenticator->get($key));
    }
}