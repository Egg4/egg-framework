<?php

namespace Egg\Orm\Repository;

class Closure extends AbstractRepository
{
    use \Egg\Yolk\ClosureAwareTrait;

    public function __construct(\Closure $closure, array $settings = [])
    {
        parent::__construct($settings);
        $this->closure = $closure;
    }

    public function execute($sql, array $params = [])
    {
        return call_user_func_array($this->closure, ['execute', [$sql, $params]]);
    }

    public function getDatabase()
    {
        return call_user_func_array($this->closure, ['getDatabase']);
    }

    public function insert(array $data)
    {
        return call_user_func_array($this->closure, ['insert', [$data]]);
    }

    public function delete(array $where = [])
    {
        return call_user_func_array($this->closure, ['delete', [$where]]);
    }

    public function update(array $data, array $where = [])
    {
        return call_user_func_array($this->closure, ['update', [$data, $where]]);
    }

    public function selectAll(array $where = [], array $orderBy = [], array $limit = [])
    {
        return call_user_func_array($this->closure, ['selectAll', [$where, $orderBy, $limit]]);
    }

    public function selectOne(array $where = [])
    {
        return call_user_func_array($this->closure, ['selectOne', [$where]]);
    }
}