<?php

namespace Egg\Component\Http\Response;

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
            'media.types'   => [],
            'charset'       => 'UTF-8',
        ], $settings);
    }

    public function run(Request $request, Response $response, Component $next)
    {
        $response = $next($request, $response);

        $body = $response->getBody();
        if (!($body instanceof \Egg\Http\Body)) {
            return $response;
        }

        $contentType = false;
        if ($response->hasHeader('Content-Type')) {
            $contentTypeLine = $response->getHeaderLine('Content-Type');
            $contentTypes = $this->parseContentTypeLine($contentTypeLine);
            $contentType = $this->findFirstMatchedContentType($contentTypes);
        }
        if (!$contentType AND $request->hasHeader('Accept')) {
            $acceptLine = $request->getHeaderLine('Accept');
            $contentTypes = $this->parseAcceptLine($acceptLine);
            $contentType = $this->findFirstMatchedContentType($contentTypes);
        }
        if (!$contentType) {
            $keys = $this->settings['media.types'];
            $contentType = array_shift($keys);
        }

        $content = $body->getContent();
        if (is_array($content)) {
            $formatter = $this->container['formatter']->get($contentType);
            $content = $formatter->format($content);
        }
        $body->rewind();
        $body->write($content);
        $response = $response->withHeader('Content-type', $contentType . '; ' . $this->settings['charset']);

        return $response;
    }

    protected function parseContentTypeLine($contentTypeLine)
    {
        list($contentTypes) = explode(';', $contentTypeLine);

        return explode(',', $contentTypes);
    }

    protected function parseAcceptLine($acceptLine)
    {
        list($contentTypes) = explode(';', $acceptLine);

        return explode(',', $contentTypes);
    }

    protected function findFirstMatchedContentType(array $contentTypes)
    {
        foreach ($contentTypes as $contentType) {
            if (in_array($contentType, $this->settings['media.types'])) {
                return $contentType;
            }
        }

        return false;
    }
}