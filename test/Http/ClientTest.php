<?php

namespace Egg\Http;

use \Egg\Container;
use \Egg\Component\Closure as ClosureComponent;

class ClientTest extends \Egg\Test
{
    public function testShouldGetData()
    {
        $environment = \Egg\Http\Environment::create([]);
        $router = new \Egg\Router();
        $router->map('test', 'GET', '/test');
        $container = new Container([
            'environment'   => $environment,
            'router'        => $router,
            'components'    => [
                new ClosureComponent(function($request, $response) {
                    $this->assertEquals('application/json', $request->getMediaType());
                    $body = $response->getBody();
                    $body->write('test');
                    return $response;
                })
            ],
        ]);
        $client = new \Egg\Http\Client($container);
        $content = $client->get('/test', ['Content-Type' => 'application/json']);

        $this->assertEquals('test', $content);
    }

    public function testShouldPostData()
    {
        $environment = \Egg\Http\Environment::create([]);
        $router = new \Egg\Router();
        $router->map('test', 'POST', '/test');
        $container = new Container([
            'environment'   => $environment,
            'router'        => $router,
            'components'    => [
                new ClosureComponent(function($request, $response) {
                    $body = $request->getBody();
                    $body->rewind();
                    $this->assertEquals('data', $body->getContents());
                    return $response->withStatus(204);
                })
            ],
        ]);
        $client = new \Egg\Http\Client($container);
        $client->post('/test', [], 'data');

        $this->assertEquals(204, $client->getResponse()->getStatusCode());
    }
}