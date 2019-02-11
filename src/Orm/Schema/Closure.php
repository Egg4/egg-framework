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

    protected function getName()
    {
        return call_user_func_array($this->closure, ['getName']);
    }

    protected function getTables()
    {
        return call_user_func_array($this->closure, ['getTables']);
    }

    protected function getColumns()
    {
        return call_user_func_array($this->closure, ['getColumns']);
    }

    protected function getForeignKeys()
    {
        return call_user_func_array($this->closure, ['getForeignKeys']);
    }

    protected function getUniqueKeys()
    {
        return call_user_func_array($this->closure, ['getUniqueKeys']);
    }
}