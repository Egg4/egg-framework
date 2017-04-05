<?php

namespace Egg\Orm\Factory;

class Closure extends AbstractFactory
{
    use \Egg\Yolk\ClosureAwareTrait;

    public function __construct(\Closure $closure)
    {
        $this->closure = $closure;
    }

    public function create(array $data = [])
    {
        return call_user_func_array($this->closure, ['create', [$data]]);
    }
}