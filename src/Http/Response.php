<?php

namespace Egg\Http;

class Response extends \Slim\Http\Response
{
    public static function createFromEnvironment(Environment $environment)
    {
        $headers = new \Slim\Http\Headers(['Content-Type' => 'text/html; charset=UTF-8']);
        $body = new \Egg\Http\Body(fopen('php://temp', 'r+'));

        return new static(200, $headers, $body);
    }

    public static function create($status = 200, array $headers = [], $body = null)
    {
        $headers = new \Slim\Http\Headers($headers);
        $body = new \Egg\Http\Body(fopen('php://temp', 'r+'), $body);

        return new static($status, $headers, $body);
    }
}