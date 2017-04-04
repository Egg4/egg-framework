<?php

namespace Egg\Component\Http\Request;

use \Egg\Component\Http\Request\Accept as AcceptComponent;

class AcceptTest extends \Egg\Test
{
    public function testShouldSetResponseContentType()
    {
        $request = \Egg\FactoryTest::createRequest([
            'HTTP_ACCEPT' => 'text/html,application/json,application/xml;q=0.9,*/*;q=0.8',
        ]);
        $response = \Egg\FactoryTest::createResponse();

        $component = new AcceptComponent([
            'media.types' => ['application/json']
        ]);
        $response = $component($request, $response);

        $this->assertContains('application/json', $response->getHeaderLine('Content-Type'));
    }

    public function testShouldThrowExceptionWithNotAcceptableContentType()
    {
        $request = \Egg\FactoryTest::createRequest([
            'HTTP_ACCEPT' => 'text/html,application/xml;q=0.9,*/*;q=0.8',
        ]);
        $response = \Egg\FactoryTest::createResponse();

        $component = new AcceptComponent([
            'media.types' => ['application/json']
        ]);

        $this->expectException(\Egg\Http\Exception::class);
        try {
            $component($request, $response);
        }
        catch (\Egg\Http\Exception $exception) {
            $this->assertEquals(406, $exception->getStatus());
            $errors = $exception->getErrors();
            $this->assertEquals('not_acceptable', $errors[0]->getName());
            throw $exception;
        }
    }
}