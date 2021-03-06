<?php

namespace Egg\Component\Http\Request;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Egg\Component\AbstractComponent;
use Egg\Interfaces\ComponentInterface as Component;

class Accept extends AbstractComponent
{
    public function __construct(array $settings = [])
    {
        $this->dependencies = [
            \Egg\Component\Http\Exception::class,
        ];

        $this->settings = array_merge([
            'media.types'        => [],
        ], $settings);
    }

    public function run(Request $request, Response $response, Component $next)
    {
        $contentType = false;
        if ($request->hasHeader('Accept')) {
            $acceptLine = $request->getHeaderLine('Accept');
            $contentTypes = $this->parseAcceptLine($acceptLine);
            $contentType = $this->findFirstMatchedContentType($contentTypes);
        }

        if (!$contentType) {
            throw new \Egg\Http\Exception($response, 406, new \Egg\Http\Error(array(
                'name'          => 'not_acceptable',
                'description'   => sprintf('"Accept" header must be in: %s', implode(', ', $this->settings['media.types'])),
            )));
        }

        $response = $response->withHeader('Content-type', $contentType);

        $response = $next($request, $response);

        return $response;
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