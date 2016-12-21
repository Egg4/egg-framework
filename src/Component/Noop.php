<?php

namespace Egg\Component;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Egg\Interfaces\ComponentInterface as Component;

class Noop extends AbstractComponent
{
    public function __construct(array $dependencies = [])
    {
        $this->dependencies = $dependencies;
    }

    public function run(Request $request, Response $response, Component $next)
    {
        return $next($request, $response);
    }
}