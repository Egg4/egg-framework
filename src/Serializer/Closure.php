<?php

namespace Egg\Serializer;

class Closure extends AbstractSerializer
{
    use \Egg\Yolk\ClosureAwareTrait;

    public function __construct(\Closure $closure)
    {
        $this->closure = $closure;
    }

    public function toArray($input) {
        return call_user_func_array($this->closure, [$input]);
    }
}