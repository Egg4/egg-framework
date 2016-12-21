<?php

namespace Egg\Formatter;

class Closure extends AbstractFormatter
{
    use \Egg\Yolk\ClosureAwareTrait;

    public function __construct(\Closure $closure)
    {
        $this->closure = $closure;
    }

    public function format(array $array)
    {
        return call_user_func_array($this->closure, [$array]);
    }
}