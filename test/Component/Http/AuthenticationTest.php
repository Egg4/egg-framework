<?php

namespace Egg\Component\Http;

use \Egg\Container;
use \Egg\Authenticator\Closure as ClosureAuthenticator;
use \Egg\Component\Http\Authentication as AuthenticationComponent;

class AuthenticationTest extends \Egg\Test
{
    public function testShouldAuthenticate()
    {
        $user = [
            'id' => 1,
            'login' => 'login@email.com',
        ];

        $container = new Container([
            'router' => \Egg\FactoryTest::createRouter(),
            'authenticator' => new ClosureAuthenticator(function($action, $arguments) use ($user) {
                $this->assertEquals('authenticate', $action);
                $this->assertEquals('apikey', $arguments[0]);
                return $user;
            }),
            'request' => \Egg\FactoryTest::createRequest([
                'REQUEST_METHOD'        => 'GET',
                'REQUEST_URI'           => '/',
                'HTTP_AUTHORIZATION'    => 'key: apikey',
            ]),
            'response' =>  \Egg\FactoryTest::createResponse(),
        ]);

        $component = new AuthenticationComponent([
            'header.key'        => 'Authorization',
            'header.pattern'    => 'key: {token}',
        ]);
        $component->setContainer($container);
        $component->init();
        $container['router']->map('read', 'GET', '/');
        $request = $container['router']->dispatch($container['request']);
        $next = function($request, $response) use ($user) {
            $this->assertEquals($user, $request->getAttribute('authentication'));
            return $response;
        };

        $component($request, $container['response'], $next->bindTo($this));
    }

    public function testShouldBypassPublicRoute()
    {
        $container = new Container([
            'router' => \Egg\FactoryTest::createRouter(),
            'request' => \Egg\FactoryTest::createRequest([
                'REQUEST_METHOD'        => 'POST',
                'REQUEST_URI'           => '/user/login',
            ]),
            'response' =>  \Egg\FactoryTest::createResponse(),
        ]);

        $component = new AuthenticationComponent([
            'header.key'        => 'Authorization',
            'header.pattern'    => 'key: {token}',
            'route.public'      => ['user:custom:login'],
        ]);
        $component->setContainer($container);
        $component->init();
        $container['router']->map('custom', 'POST', '/{resource}/{action}');
        $request = $container['router']->dispatch($container['request']);
        $next = function($request, $response) {
            $this->assertEmpty($request->getAttribute('authentication'));
            return $response;
        };

        $component($request, $container['response'], $next->bindTo($this));
    }

    public function testShouldRaiseAuthenticationRequiredException()
    {
        $container = new Container([
            'router' => \Egg\FactoryTest::createRouter(),
            'authenticator' => new ClosureAuthenticator(function() {
                return false;
            }),
            'request' => \Egg\FactoryTest::createRequest([
                'REQUEST_METHOD'        => 'GET',
                'REQUEST_URI'           => '/',
                'HTTP_AUTHORIZATION'    => 'key: apikey',
            ]),
            'response' =>  \Egg\FactoryTest::createResponse(),
        ]);

        $component = new AuthenticationComponent([
            'header.key'        => 'Authorization',
            'header.pattern'    => 'key: {token}',
        ]);
        $component->setContainer($container);
        $component->init();
        $container['router']->map('read', 'GET', '/');
        $request = $container['router']->dispatch($container['request']);

        $this->expectException(\Egg\Http\Exception::class);
        try {
            $component($request, $container['response']);
        }
        catch (\Egg\Http\Exception $exception) {
            $this->assertEquals(403, $exception->getStatus());
            $errors = $exception->getErrors();
            $this->assertEquals('authentication_required', $errors[0]->getName());
            throw $exception;
        }
    }
}