<?php

namespace Egg\Http;

class Request extends \Slim\Http\Request
{
    public static function createFromEnvironment(\Slim\Http\Environment $environment)
    {
        $method = $environment['REQUEST_METHOD'];
        $uri = \Slim\Http\Uri::createFromEnvironment($environment);
        $headers = \Slim\Http\Headers::createFromEnvironment($environment);
        $cookies = \Slim\Http\Cookies::parseHeader($headers->get('Cookie', []));
        $serverParams = $environment->all();
        $stream = fopen('php://temp', 'w+');
        stream_copy_to_stream(fopen('php://input', 'r'), $stream);
        rewind($stream);
        $body = new Body($stream);
        $uploadedFiles = \Slim\Http\UploadedFile::createFromEnvironment($environment);

        return new static($method, $uri, $headers, $cookies, $serverParams, $body, $uploadedFiles);
    }

    public static function create($env = [], array $headers = [], $body = null)
    {
        $environment = is_array($env) ? \Egg\Http\Environment::create($env) : $env;
        $method = $environment['REQUEST_METHOD'];
        $uri = \Slim\Http\Uri::createFromEnvironment($environment);
        $envHeaders = \Slim\Http\Headers::createFromEnvironment($environment);
        foreach($headers as $name => $value) {
            $envHeaders->set($name, $value);
        }
        $headers = $envHeaders;
        $cookies = \Slim\Http\Cookies::parseHeader($headers->get('Cookie', []));
        $serverParams = $environment->all();
        $body = new Body(fopen('php://temp', 'r+'), $body);
        if (is_string($body->getContent())) {
            $body->write($body->getContent());
        }
        $uploadedFiles = \Slim\Http\UploadedFile::createFromEnvironment($environment);

        return new static($method, $uri, $headers, $cookies, $serverParams, $body, $uploadedFiles);
    }
}