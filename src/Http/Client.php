<?php

namespace Egg\Http;

use Egg\Container;

class Client
{
    protected $container;
    protected $response;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    protected function send($method, $uri, $headers = [], $body = null)
    {
        $this->container['request'] = $this->buildRequest($method, $uri, $headers, $body);
        $this->container['response'] = $this->buildResponse();

        $app = new \Egg\App($this->container);
        $this->response = $app->run(true);
        $body = $this->response->getBody();
        $body->rewind();

        return $body->getContents();
    }

    protected function buildRequest($method, $uri, $headers, $body)
    {
        $method = strtoupper($method);
        $uri = \Slim\Http\Uri::createFromString($uri);
        $headers = new \Slim\Http\Headers($headers);
        $cookies = \Slim\Http\Cookies::parseHeader($headers->get('Cookie', []));
        $serverParams = $this->container['environment']->all();
        $body = new Body(fopen('php://temp', 'r+'), $body);
        if (is_string($body->getContent())) {
            $body->write($body->getContent());
        }
        return new Request($method, $uri, $headers, $cookies, $serverParams, $body);
    }

    protected function buildResponse($status = 200, array $headers = [], $body = null)
    {
        $headers = new \Slim\Http\Headers($headers);
        $body = new Body(fopen('php://temp', 'r+'), $body);

        return  new Response($status, $headers, $body);
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function get($uri, $headers = [])
    {
        return $this->send('get', $uri, $headers);
    }

    public function post($uri, $headers = [], $body = null)
    {
        return $this->send('post', $uri, $headers, $body);
    }

    public function patch($uri, $headers = [], $body = null)
    {
        return $this->send('patch', $uri, $headers, $body);
    }

    public function put($uri, $headers = [], $body = null)
    {
        return $this->send('put', $uri, $headers, $body);
    }

    public function delete($uri, $headers = [])
    {
        return $this->send('delete', $uri, $headers);
    }

    public function options($uri, $headers = [])
    {
        return $this->send('options', $uri, $headers);
    }
}