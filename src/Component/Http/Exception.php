<?php

namespace Egg\Component\Http;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Egg\Component\AbstractComponent;
use Egg\Interfaces\ComponentInterface as Component;
use Egg\Interfaces\SerializerInterface as Serializer;

class Exception extends AbstractComponent
{
    public function __construct(array $settings = [])
    {
        $this->dependencies = [
            \Egg\Component\Http\Response\ContentType::class,
        ];

        $this->settings = array_merge([
            'serializer'  => new \Egg\Serializer\Error(),
        ], $settings);
    }

    public function run(Request $request, Response $response, Component $next)
    {
        try {
            $response = $next($request, $response);
        }
        catch (\Egg\Http\Exception $exception) {
            $response = $response->withStatus($exception->getStatus());
            $array = $this->settings['serializer']->serialize($exception->getErrors());
            $response->getBody()->setContent($array);
        }
        catch (\Exception $exception) {
            $response = $response->withStatus(500);
            $array = $this->settings['serializer']->serialize(new \Egg\Http\Error(array(
                'name'        => 'server_error',
                'description' => 'Oops! Something went wrong...',
                'uri'         => '',
            )));
            $response->getBody()->setContent($array);
        }

        return $response;
    }
}