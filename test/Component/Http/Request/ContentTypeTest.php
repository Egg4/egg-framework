<?php

namespace Egg\Component\Http\Request;

use \Egg\Container;
use \Egg\Component\Http\Request\ContentType as ContentTypeComponent;
use \Egg\Parser\Closure as ClosureParser;

class ContentTypeTest extends \Egg\Test
{
    public function testShouldReturnParsedBody()
    {
        $data = array(
            'id' => 24,
            'name' => 'test',
        );

        $request = \Egg\FactoryTest::createRequest([], ['Content-Type' => 'application/json;charset=utf8'], json_encode($data));
        $response = \Egg\FactoryTest::createResponse();
        $container = new Container([
            'parser' => new Container([
                'application/json' => function() {
                    return new \Egg\Parser\Json();
            }]),
        ]);

        $component = new ContentTypeComponent([
            'media.types' => ['application/json']
        ]);
        $component->setContainer($container);
        $next = function($request, $response) use ($data) {
            $content = $request->getBody()->getContent();
            $this->assertEquals($content, $data);
            return $response;
        };
        $component($request, $response, $next->bindTo($this));
    }

    public function testShouldThrowExceptionWithUnsupportedContentType()
    {
        $request = \Egg\FactoryTest::createRequest([], ['Content-Type' => 'application/xml;charset=utf8']);
        $response = \Egg\FactoryTest::createResponse();
        $container = new Container([
            'parser' => new Container([
                'application/json' => function() {
                    return new \Egg\Parser\Json();
            }]),
        ]);

        $component = new ContentTypeComponent([
            'media.types' => ['application/json']
        ]);
        $component->setContainer($container);

        $this->expectException(\Egg\Http\Exception::class);
        try {
            $component($request, $response);
        }
        catch (\Egg\Http\Exception $exception) {
            $this->assertEquals(415, $exception->getStatus());
            $errors = $exception->getErrors();
            $this->assertEquals('unsupported_content_type', $errors[0]->getName());
            throw $exception;
        }
    }

    public function testShouldThrowExceptionWithUnparsableContentType()
    {
        $request = \Egg\FactoryTest::createRequest([], ['Content-Type' => 'application/json;charset=utf8']);
        $response = \Egg\FactoryTest::createResponse();
        $container = new Container([
            'parser' => new Container([
                'application/json' => function() {
                    return new ClosureParser(function() {
                        throw new \Exception('Malformed json');
                    });
                },
            ])
        ]);

        $component = new ContentTypeComponent([
            'media.types' => ['application/json']
        ]);
        $component->setContainer($container);

        $this->expectException(\Egg\Http\Exception::class);
        try {
            $component($request, $response);
        }
        catch (\Egg\Http\Exception $exception) {
            $this->assertEquals(400, $exception->getStatus());
            $errors = $exception->getErrors();
            $this->assertEquals('unparsable_content_type', $errors[0]->getName());
            throw $exception;
        }
    }
}