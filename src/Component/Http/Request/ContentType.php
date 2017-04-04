<?php

namespace Egg\Component\Http\Request;

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
            \Egg\Component\Http\Exception::class,
        ];

        $this->settings = array_merge([
            'media.types'        => [],
        ], $settings);
    }

    public function run(Request $request, Response $response, Component $next)
    {
        if ($request->hasHeader('Content-Type')) {
            $contentType = $request->getMediaType();
            if ($contentType AND !in_array($contentType, $this->settings['media.types'])) {
                throw new \Egg\Http\Exception($response, 415, new \Egg\Http\Error(array(
                    'name'          => 'unsupported_content_type',
                    'description'   => sprintf('"Content-Type" header must be in: %s', implode(', ', $this->settings['media.types'])),
                )));
            }
            $parser = $this->container['parser']->get($contentType);
            try {
                $body = $request->getBody();
                $body->rewind();
                $string = $body->getContents();
                $content = $parser->parse($string);
                $body->setContent($content);
            }
            catch (\Exception $exception) {
                throw new \Egg\Http\Exception($response, 400, new \Egg\Http\Error(array(
                    'name'          => 'unparsable_content_type',
                    'description'   => $exception->getMessage(),
                )));
            }
        }

        $response = $next($request, $response);

        return $response;
    }
}