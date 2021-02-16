<?php

namespace Egg\Component\Resource;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Egg\Component\AbstractComponent;
use Egg\Interfaces\ComponentInterface as Component;

class Range extends AbstractComponent
{
    public function __construct(array $settings = [])
    {
        $this->dependencies = [
            \Egg\Component\Http\Route::class,
        ];
        
        $this->settings = array_merge([
            'routes'    => ['select', 'search'],
            'key'       => 'range',
            'pattern'   => '/(\d+)-(\d+)/',
        ], $settings);
    }
    
    public function run(Request $request, Response $response, Component $next)
    {
        $route = $request->getAttribute('route');
        if (in_array($route->getName(), $this->settings['routes'])) {
            $range = $request->getQueryParam($this->settings['key'], '');
            if (empty($range)) {
                $request = $request->withAttribute('range', []);
            } else {
                preg_match($this->settings['pattern'], $range, $matches);
                if (count($matches) != 3) {
                    throw new \Egg\Http\Exception($response, 400, new \Egg\Http\Error(array(
                        'name'          => 'unparsable_range',
                        'description'   => sprintf('Range "%s" is not parsable', $range),
                    )));
                }
                $start = $matches[1];
                $end = $matches[2];
                if (!is_numeric($start) OR !is_numeric($end) OR $start > $end) {
                    throw new \Egg\Http\Exception($response, 400, new \Egg\Http\Error(array(
                        'name'          => 'invalid_range',
                        'description'   => sprintf('Range "%s" is not valid', $range),
                    )));
                }
                $request = $request->withAttribute('range', [
                    'offset'    => (int) $start,
                    'limit'     => (int) ($end - $start + 1),
                ]);
            }
        }
        
        $response = $next($request, $response);
        
        return $response;
    }
}