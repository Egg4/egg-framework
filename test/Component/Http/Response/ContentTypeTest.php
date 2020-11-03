<?php

namespace Egg\Component\Response;

use \PHPUnit\Framework\TestCase;
use \Egg\Container;
use \Egg\Component\Http\Response\ContentType as ContentTypeComponent;

class ContentTypeTest extends TestCase
{
    public function testShouldReturnFormattedBody()
    {
        $data = array(
            'id' => 24,
            'name' => 'test',
        );

        $request = \Egg\FactoryTest::createRequest();
        $response = \Egg\FactoryTest::createResponse(200, ['Content-Type' => 'application/xml,application/json'], $data);
        $container = new Container([
            'formatter' => new Container([
                'application/json' => function() {
                    return new \Egg\Formatter\Json();
                },
            ])
        ]);

        $component = new ContentTypeComponent([
            'media.types' => ['application/json']
        ]);
        $component->setContainer($container);

        $response = $component($request, $response);
        $body = $response->getBody();
        $body->rewind();
        $this->assertEquals($body->getContents(), json_encode($data));
    }

    public function testShouldShouldReturnDefaultFormattedBody()
    {
        $data = array(
            'id' => 24,
            'name' => 'test',
        );

        $request = \Egg\FactoryTest::createRequest();
        $response = \Egg\FactoryTest::createResponse(200, [], $data);
        $container = new Container([
            'formatter' => new Container([
                'application/json' => function() {
                    return new \Egg\Formatter\Json();
                },
            ])
        ]);

        $component = new ContentTypeComponent([
            'media.types' => ['application/json']
        ]);
        $component->setContainer($container);

        $response = $component($request, $response);
        $body = $response->getBody();
        $body->rewind();
        $this->assertEquals($body->getContents(), json_encode($data));
    }
}