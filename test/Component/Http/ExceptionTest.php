<?php

namespace Egg\Component\Http;

use \Egg\Component\Http\Exception as ExceptionComponent;

class ExceptionTest extends \Egg\Test
{
    public function testShouldReturn200()
    {
        $request = \Egg\FactoryTest::createRequest();
        $response = \Egg\FactoryTest::createResponse();

        $component = new ExceptionComponent([
            'serializer' => new \Egg\Serializer\Error(),
        ]);
        $response = $component($request, $response);
        $responseStatus = $response->getStatusCode();

        $this->assertEquals($responseStatus, 200);
    }

    public function testShouldReturn404()
    {
        $request = \Egg\FactoryTest::createRequest();
        $response = \Egg\FactoryTest::createResponse();

        $component = new ExceptionComponent([
            'serializer' => new \Egg\Serializer\Error(),
        ]);
        $response = $component($request, $response, function() use ($response) {
            throw new \Egg\Http\Exception($response, 404, new \Egg\Http\Error(array(
                'name' => 'not_found',
            )));
        });
        $responseStatus = $response->getStatusCode();
        $content = $response->getBody()->getContent();

        $this->assertEquals($responseStatus, 404);
        $this->assertEquals($content[0]['name'], 'not_found');
    }

    public function testShouldReturn500()
    {
        $request = \Egg\FactoryTest::createRequest();
        $response = \Egg\FactoryTest::createResponse();

        $component = new ExceptionComponent([
            'serializer' => new \Egg\Serializer\Error(),
        ]);
        $response = $component($request, $response, function() {
            throw new \Exception();
        });
        $responseStatus = $response->getStatusCode();
        $content = $response->getBody()->getContent();

        $this->assertEquals($responseStatus, 500);
        $this->assertEquals($content[0]['name'], 'server_error');
    }
}