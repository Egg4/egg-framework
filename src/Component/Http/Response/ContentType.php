<?php

namespace Egg\Component\Http\Response;

use Egg\Container;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Egg\Component\AbstractComponent;
use Egg\Interfaces\ComponentInterface as Component;

class ContentType extends AbstractComponent
{
    public function __construct(array $settings = [])
    {
        $this->dependencies = [

        ];

        $this->settings = array_merge([
            'contentTypes'  => ['application/json', 'application/xml'],
        ], $settings);
    }

    public function run(Request $request, Response $response, Component $next)
    {
        $response = $next($request, $response);

        $body = $response->getBody();
        if (!($body instanceof \Egg\Http\Body)) {
            return $response;
        }

        $keys = $this->settings['contentTypes'];
        $contentType = array_shift($keys);
        if ($response->hasHeader('Content-Type')) {
            $contentTypeLine = $response->getHeaderLine('Content-Type');
            $contentTypes = $this->parseContentTypeLine($contentTypeLine);
            $contentType = $this->findFirstMatchedContentType($contentTypes);
        }

        $formatter = $this->container['formatter']->get($contentType);
        $string = $formatter->format($body->getContent());
        $body->rewind();
        $body->write($string);

        return $response;
    }

    protected function parseContentTypeLine($contentTypeLine)
    {
        list($contentTypes) = explode(';', $contentTypeLine);

        return explode(',', $contentTypes);
    }

    protected function findFirstMatchedContentType(array $contentTypes)
    {
        foreach ($contentTypes as $contentType) {
            if (in_array($contentType, $this->settings['contentTypes'])) {
                return $contentType;
            }
        }

        return false;
    }
}