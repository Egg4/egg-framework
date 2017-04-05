<?php

namespace Egg\Orm\Database;

class Closure extends AbstractDatabase
{
    use \Egg\Yolk\ClosureAwareTrait;

    public function __construct(\Closure $closure, array $settings = [])
    {
        parent::__construct($settings);
        $this->closure = $closure;
    }

    public function getName()
    {
        return call_user_func_array($this->closure, ['getName']);
    }

    public function execute($sql, array $params = [])
    {
        return call_user_func_array($this->closure, ['execute', [$sql, $params]]);
    }

    public function lastInsertId()
    {
        return call_user_func_array($this->closure, ['lastInsertId']);
    }

    public function beginTransaction()
    {
        return call_user_func_array($this->closure, ['beginTransaction']);
    }

    public function commit()
    {
        return call_user_func_array($this->closure, ['commit']);
    }

    public function rollback()
    {
        return call_user_func_array($this->closure, ['rollback']);
    }
}