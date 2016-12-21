<?php

namespace Egg\Component\Http\Request;

use \Egg\Component\Http\Request\AcceptLanguage as AcceptLanguageComponent;

class AcceptLanguageTest extends \Egg\Test
{
    public function testShouldSetResponseContentType()
    {
        $request = \Egg\FactoryTest::createRequest([
            'HTTP_ACCEPT_LANGUAGE' => 'fr,fr-FR;q=0.8,en-US;q=0.5,en;q=0.3',
        ]);
        $response = \Egg\FactoryTest::createResponse();

        $component = new AcceptLanguageComponent([
            'languages' => ['fr']
        ]);
        $response = $component($request, $response);

        $this->assertContains('fr', $response->getHeaderLine('Content-Language'));
    }

    public function testShouldThrowExceptionWithNotAcceptableContentType()
    {
        $request = \Egg\FactoryTest::createRequest([
            'HTTP_ACCEPT_LANGUAGE' => 'fr,fr-FR;q=0.8,en-US;q=0.5,en;q=0.3',
        ]);
        $response = \Egg\FactoryTest::createResponse();

        $component = new AcceptLanguageComponent([
            'languages' => ['de']
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