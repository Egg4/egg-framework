<?php

namespace Egg\Authorizer;

class Closure extends AbstractAuthorizer
{
    use \Egg\Yolk\ClosureAwareTrait;

    public function __construct(\Closure $closure)
    {
        $this->closure = $closure;
    }

    public function authorize($action, array $arguments = [])
    {
        return call_user_func_array($this->closure, [$action, $arguments]);
    }
}