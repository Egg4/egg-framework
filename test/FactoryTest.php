<?php

namespace Egg;

abstract class FactoryTest
{
    public static function createRequest(array $env = [], array $headers = [], $body = null)
    {
        $environment = \Slim\Http\Environment::mock($env);
        $request = \Slim\Http\Request::createFromEnvironment($environment);

        foreach($headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        $body = new \Egg\Http\Body(fopen('php://temp', 'r+'), $body);
        $request = $request->withBody($body);
        if (is_string($body->getContent())) {
            $request->getBody()->write($body->getContent());
        }

        return $request;
    }

    public static function createResponse($status = 200, array $headers = [], $body = null)
    {
        $headers = new \Slim\Http\Headers($headers);
        $body = new \Egg\Http\Body(fopen('php://temp', 'r+'), $body);
        $response = new \Slim\Http\Response($status, $headers, $body);

        return $response;
    }

    public static function createRouter()
    {
        return new \Egg\Router();
    }
}