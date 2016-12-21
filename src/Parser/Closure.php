<?php

namespace Egg\Parser;

class Closure extends AbstractParser
{
    use \Egg\Yolk\ClosureAwareTrait;

    public function __construct(\Closure $closure)
    {
        $this->closure = $closure;
    }

    public function parse($string)
    {
        return call_user_func_array($this->closure, [$string]);
    }
}