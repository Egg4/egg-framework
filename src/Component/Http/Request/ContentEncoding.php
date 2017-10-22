<?php

namespace Egg\Component\Http\Request;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Egg\Component\AbstractComponent;
use Egg\Interfaces\ComponentInterface as Component;

class ContentEncoding extends AbstractComponent
{
    public function __construct(array $settings = [])
    {
        $this->dependencies = [
            \Egg\Component\Http\Exception::class,
        ];

        $this->settings = array_merge([
            'media.encodings'   => [],
        ], $settings);
    }

    public function run(Request $request, Response $response, Component $next)
    {
        if ($request->hasHeader('Content-Encoding')) {
            $contentEncoding = $request->getHeaderLine('Content-Encoding');
            if (!in_array($contentEncoding, $this->settings['media.encodings'])) {
                throw new \Egg\Http\Exception($response, 415, new \Egg\Http\Error(array(
                    'name'          => 'unsupported_content_encoding',
                    'description'   => sprintf('"Content-Encoding" header must be in: %s', implode(', ', $this->settings['media.encodings'])),
                )));
            }
            try {
                $body = $request->getBody();
                $body->rewind();
                $content = $body->getContent();
                if (strlen($content) > 0) {
                    $content = gzinflate(substr($content, 10, -8));
                }
                $body->setContent($content);
            }
            catch (\Exception $exception) {
                throw new \Egg\Http\Exception($response, 400, new \Egg\Http\Error(array(
                    'name'          => 'undecodable_content_encoding',
                    'description'   => $exception->getMessage(),
                )));
            }
        }

        $response = $next($request, $response);

        return $response;
    }
}