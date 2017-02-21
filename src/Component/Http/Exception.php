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
            set_error_handler(function ($err_severity, $err_msg, $err_file, $err_line, array $err_context) {
                if (0 === error_reporting()) return false;
                throw new \ErrorException($err_msg, 0, $err_severity, $err_file, $err_line);
            });

            $response = $next($request, $response);
        }
        catch (\Egg\Http\Exception $exception) {
            $response = $exception->getResponse();
            $response = $response->withStatus($exception->getStatus());
            $array = $this->settings['serializer']->serialize($exception->getErrors());
            $response->getBody()->setContent($array);
        }
        catch (\Exception $exception) {
            if ($this->container['environment']['APP_DEBUG']) {
                throw $exception;
            }
            $response = $this->container['response'] ? $this->container['response'] : $response;
            $response = $response->withStatus(500);
            if ($this->container['environment']['APP_ENV'] == 'dev') {
                $message = sprintf('%s in %s:%s', $exception->getMessage(), $exception->getFile(), $exception->getLine());
            }
            else {
                $message = 'Oops! Something went wrong...';
            }
            $array = $this->settings['serializer']->serialize([new \Egg\Http\Error(array(
                'name'        => 'server_error',
                'description' => $message,
                'uri'         => '',
            ))]);
            $response->getBody()->setContent($array);
        }

        return $response;
    }
}