<?php

namespace Egg\Component\Http;

use \Egg\Component\Http\Cors as CorsComponent;

class CorsTest extends \Egg\Test
{
    public function testShouldReturn200ByDefault()
    {
        $request = \Egg\FactoryTest::createRequest([
            'REQUEST_SCHEME'        => 'http',
            'HTTP_HOST'             => 'www.example.com',
            'REQUEST_METHOD'        => 'GET',
            'REQUEST_URI'           => '/api',
        ]);
        $response = \Egg\FactoryTest::createResponse();

        $component = new CorsComponent();
        $response = $component($request, $response);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testShouldHaveCorsHeaders()
    {
        $request = \Egg\FactoryTest::createRequest([
            'REQUEST_SCHEME'        => 'http',
            'HTTP_HOST'             => 'www.example.com',
            'REQUEST_METHOD'        => 'GET',
            'REQUEST_URI'           => '/api',
        ], [
            'Origin'                => 'http://www.example.com',
        ]);
        $response = \Egg\FactoryTest::createResponse();

        $component = new CorsComponent([
            'origin' => '*',
            'methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'],
            'headers.allow' => ['Authorization', 'If-Match', 'If-Unmodified-Since'],
            'headers.expose' => ['Authorization', 'Etag'],
            'credentials' => true,
            'cache' => 86400
        ]);
        $response = $component($request, $response);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('http://www.example.com', $response->getHeaderLine('Access-Control-Allow-Origin'));
        $this->assertEquals('true', $response->getHeaderLine('Access-Control-Allow-Credentials'));
        $this->assertEquals('Origin', $response->getHeaderLine('Vary'));
        $this->assertEquals('Authorization,Etag', $response->getHeaderLine('Access-Control-Expose-Headers'));
    }

    public function testShouldReturn401WithWrongOrigin()
    {
        $request = \Egg\FactoryTest::createRequest([
            'REQUEST_SCHEME'        => 'http',
            'HTTP_HOST'             => 'www.example.com',
            'REQUEST_METHOD'        => 'GET',
            'REQUEST_URI'           => '/api',
        ], [
            'Origin'                => 'http://www.foo.com',
        ]);
        $response = \Egg\FactoryTest::createResponse();

        $component = new CorsComponent([
            'origin' => 'http://www.example.com',
            'methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'],
            'headers.allow' => ['Authorization', 'If-Match', 'If-Unmodified-Since'],
            'headers.expose' => ['Authorization', 'Etag'],
            'credentials' => true,
            'cache' => 86400
        ]);

        $this->expectException(\Egg\Http\Exception::class);
        try {
            $component($request, $response);
        }
        catch (\Egg\Http\Exception $exception) {
            $this->assertEquals(401, $exception->getStatus());
            $errors = $exception->getErrors();
            $this->assertEquals('not_allowed_cors_origin', $errors[0]->getName());
            throw $exception;
        }
    }

    public function testShouldReturn200WithCorrectOrigin()
    {
        $request = \Egg\FactoryTest::createRequest([
            'REQUEST_SCHEME'        => 'http',
            'HTTP_HOST'             => 'www.example.com',
            'REQUEST_METHOD'        => 'GET',
            'REQUEST_URI'           => '/api',
        ], [
            'Origin'                => 'http://mobile.example.com',
        ]);
        $response = \Egg\FactoryTest::createResponse();

        $component = new CorsComponent([
            'origin' => ['http://www.example.com', 'http://mobile.example.com'],
            'methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'],
            'headers.allow' => ['Authorization', 'If-Match', 'If-Unmodified-Since'],
            'headers.expose' => ['Authorization', 'Etag'],
            'credentials' => true,
            'cache' => 86400
        ]);

        $response = $component($request, $response);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('http://mobile.example.com', $response->getHeaderLine('Access-Control-Allow-Origin'));
    }

    public function testShouldReturn401WithWrongMethod()
    {
        $request = \Egg\FactoryTest::createRequest([
            'REQUEST_SCHEME'                    => 'http',
            'HTTP_HOST'                         => 'www.example.com',
            'REQUEST_METHOD'                    => 'OPTIONS',
            'REQUEST_URI'                       => '/api',
        ], [
            'Origin'                            => 'http://www.foo.com',
            'Access-Control-Request-Headers'    => 'Authorization',
            'Access-Control-Request-Method'     => 'PUT',
        ]);
        $response = \Egg\FactoryTest::createResponse();

        $component = new CorsComponent([
            'origin' => '*',
            'methods' => ['GET', 'POST', 'PATCH', 'DELETE'],
            'headers.allow' => ['Authorization', 'If-Match', 'If-Unmodified-Since'],
            'headers.expose' => ['Authorization', 'Etag'],
            'credentials' => true,
            'cache' => 86400
        ]);

        $this->expectException(\Egg\Http\Exception::class);
        try {
            $component($request, $response);
        }
        catch (\Egg\Http\Exception $exception) {
            $this->assertEquals(401, $exception->getStatus());
            $errors = $exception->getErrors();
            $this->assertEquals('unsupported_cors_method', $errors[0]->getName());
            throw $exception;
        }
    }

    public function testShouldReturn401WithWrongHeader()
    {
        $request = \Egg\FactoryTest::createRequest([
            'REQUEST_SCHEME'                    => 'http',
            'HTTP_HOST'                         => 'www.example.com',
            'REQUEST_METHOD'                    => 'OPTIONS',
            'REQUEST_URI'                       => '/api',
        ], [
            'Origin'                            => 'http://www.foo.com',
            'Access-Control-Request-Headers'    => 'X-Nosuch',
            'Access-Control-Request-Method'     => 'PUT',
        ]);
        $response = \Egg\FactoryTest::createResponse();

        $component = new CorsComponent([
            'origin' => '*',
            'methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'],
            'headers.allow' => ['Authorization', 'If-Match', 'If-Unmodified-Since'],
            'headers.expose' => ['Authorization', 'Etag'],
            'credentials' => true,
            'cache' => 86400
        ]);

        $this->expectException(\Egg\Http\Exception::class);
        try {
            $component($request, $response);
        }
        catch (\Egg\Http\Exception $exception) {
            $this->assertEquals(401, $exception->getStatus());
            $errors = $exception->getErrors();
            $this->assertEquals('unsupported_cors_headers', $errors[0]->getName());
            throw $exception;
        }
    }

    public function testShouldReturn200WithProperPreflightRequest()
    {
        $request = \Egg\FactoryTest::createRequest([
            'REQUEST_SCHEME'                    => 'http',
            'HTTP_HOST'                         => 'www.example.com',
            'REQUEST_METHOD'                    => 'OPTIONS',
            'REQUEST_URI'                       => '/api',
        ], [
            'Origin'                            => 'http://mobile.example.com',
            'Access-Control-Request-Headers'    => 'Authorization',
            'Access-Control-Request-Method'     => 'PUT',
        ]);
        $response = \Egg\FactoryTest::createResponse();

        $component = new CorsComponent([
            'origin' => '*',
            'methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'],
            'headers.allow' => ['Authorization', 'If-Match', 'If-Unmodified-Since'],
            'headers.expose' => ['Authorization', 'Etag'],
            'credentials' => true,
            'cache' => 86400
        ]);

        $response = $component($request, $response);

        $this->assertEquals(200, $response->getStatusCode());
    }
}