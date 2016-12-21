<?php

namespace Egg\Resolver;

class Closure extends AbstractResolver
{
    use \Egg\Yolk\ClosureAwareTrait;

    public function __construct(\Closure $closure)
    {
        $this->closure = $closure;
    }

    public function resolve()
    {
        return call_user_func_array($this->closure, func_get_args());
    }
}