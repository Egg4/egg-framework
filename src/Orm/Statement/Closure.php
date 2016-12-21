<?php

namespace Egg\Orm\Statement;

class Closure extends AbstractStatement
{
    use \Egg\Yolk\ClosureAwareTrait;

    public function __construct(\Closure $closure)
    {
        $this->closure = $closure;
    }

    public function entityCount()
    {
        return call_user_func_array($this->closure, ['entityCount']);
    }

    public function fetchEntitySet($entitySetClass, $entityClass)
    {
        return call_user_func_array($this->closure, ['fetchEntitySet', [$entitySetClass, $entityClass]]);
    }

    public function fetchEntity($entityClass)
    {
        return call_user_func_array($this->closure, ['fetchEntity', [$entityClass]]);
    }
}