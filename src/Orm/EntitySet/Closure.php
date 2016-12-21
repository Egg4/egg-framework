<?php

namespace Egg\Orm\EntitySet;

class Closure extends AbstractEntitySet
{
    use \Egg\Yolk\ClosureAwareTrait;

    public function __construct(\Closure $closure)
    {
        $this->closure = $closure;
    }
}