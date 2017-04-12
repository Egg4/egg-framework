<?php

namespace Egg;

abstract class FactoryTest
{
    public static function createRequest(array $env = [], array $headers = [], $body = null)
    {
        return \Egg\Http\Request::create($env, $headers, $body);
    }

    public static function createResponse($status = 200, array $headers = [], $body = null)
    {
        return \Egg\Http\Response::create($status, $headers, $body);
    }

    public static function createRouter()
    {
        return new \Egg\Router();
    }

    public static function createPdoDatabase()
    {
        return new \Egg\Orm\Database\Pdo([
            'driver'    => 'mysql',
            'host'      => 'localhost',
            'dbname'    => 'test',
            'login'     => 'root',
            'password'  => '536546',
        ]);
    }
}