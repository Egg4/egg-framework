<?php

namespace Egg\Http;

use Egg\Container;

class Client
{
    protected $container;
    protected $response;
    
    public function __construct(Container $container, array $headers = [])
    {
        $this->container = $container;
        $this->setHeaders($headers);
    }
    
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;
    }
    
    public function setHeader($key, $value)
    {
        $this->headers[$key] = $value;
    }
    
    public function removeHeader($key)
    {
        unset($this->headers[$key]);
    }
    
    public function getHeaders()
    {
        return $this->headers;
    }
    
    public function getHeader($key)
    {
        return isset($this->headers[$key]) ? $this->headers[$key] : false;
    }
    
    protected function send($method, $uri, array $headers = [], $body = null)
    {
        $this->container['request'] = $this->buildRequest($method, $uri, $headers, $body);
        $this->container['response'] = $this->buildResponse();
        
        $app = new \Egg\App($this->container);
        $this->response = $app->run(true);
        $body = $this->response->getBody();
        $body->rewind();
        $content = $body->getContents();
        
        // Parse body
        if (!empty($content)
            AND $this->container->has('parser')
            AND $this->response->hasHeader('Content-Type')) {
                $contentTypeLine = $this->response->getHeaderLine('Content-Type');
                if ($contentTypeLine) {
                    $contentTypeParts = preg_split('/\s*[;,]\s*/', $contentTypeLine);
                    $contentType = strtolower($contentTypeParts[0]);
                    if ($this->container['parser']->has($contentType)) {
                        $parser = $this->container['parser']->get($contentType);
                        return $parser->parse($content);
                    }
                }
            }
            
            return $content;
    }
    
    protected function buildRequest($method, $uri, array $headers, $body)
    {
        $method = strtoupper($method);
        $uri = \Slim\Http\Uri::createFromString($uri);
        $uri = $uri->withPath(str_replace('-', '_', $uri->getPath()));
        $headers = new \Slim\Http\Headers(array_merge($this->headers, $headers));
        $cookies = \Slim\Http\Cookies::parseHeader($headers->get('Cookie', []));
        $serverParams = $this->container['environment']->all();
        
        // Format body
        if ($this->container->has('formatter') AND $headers->has('Content-Type')) {
            $contentTypeLine = implode(',', $headers->get('Content-Type', []));
            if ($contentTypeLine) {
                $contentTypeParts = preg_split('/\s*[;,]\s*/', $contentTypeLine);
                $contentType = strtolower($contentTypeParts[0]);
                if ($this->container['formatter']->has($contentType)) {
                    $formatter = $this->container['formatter']->get($contentType);
                    $content = $body != null ? $body : [];
                    $body = $formatter->format($content);
                }
            }
        }
        $body = new Body(fopen('php://temp', 'r+'), $body);
        $body->write($body->getContent());
        
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
    
    public function get($uri, array $headers = [])
    {
        return $this->send('get', $uri, $headers);
    }
    
    public function post($uri, array $headers = [], $body = null)
    {
        return $this->send('post', $uri, $headers, $body);
    }
    
    public function patch($uri, array $headers = [], $body = null)
    {
        return $this->send('patch', $uri, $headers, $body);
    }
    
    public function put($uri, array $headers = [], $body = null)
    {
        return $this->send('put', $uri, $headers, $body);
    }
    
    public function delete($uri, array $headers = [])
    {
        return $this->send('delete', $uri, $headers);
    }
    
    public function options($uri, array $headers = [])
    {
        return $this->send('options', $uri, $headers);
    }
}