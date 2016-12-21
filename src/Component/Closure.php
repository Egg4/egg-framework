<?php

namespace Egg\Component;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Egg\Interfaces\ComponentInterface as Component;

class Closure extends AbstractComponent
{
    use \Egg\Yolk\ClosureAwareTrait;

    public function __construct(\Closure $closure)
    {
        $this->closure = $closure;
    }

    public function run(Request $request, Response $response, Component $next)
    {
        return call_user_func_array($this->closure, [$request, $response, $next]);
    }
}