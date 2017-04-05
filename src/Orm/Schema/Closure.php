<?php

namespace Egg\Orm\Schema;

class Closure extends AbstractSchema
{
    use \Egg\Yolk\ClosureAwareTrait;

    public function __construct(\Closure $closure)
    {
        $this->closure = $closure;
    }

    public function getData()
    {
        return call_user_func_array($this->closure, ['getData']);
    }
}