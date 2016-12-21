<?php

namespace Egg\Orm\Entity;

class Closure extends AbstractEntity
{
    use \Egg\Yolk\ClosureAwareTrait;

    public function __construct(\Closure $closure)
    {
        $this->closure = $closure;
    }

    public function get($key)
    {
        return call_user_func_array($this->closure, ['get', [$key]]);
    }

    public function set($key, $value)
    {
        return call_user_func_array($this->closure, ['set', [$key, $value]]);
    }

    public function has($key)
    {
        return call_user_func_array($this->closure, ['has', [$key]]);
    }

    public function detach($key)
    {
        return call_user_func_array($this->closure, ['detach', [$key]]);
    }

    public function hydrate(array $data)
    {
        return call_user_func_array($this->closure, ['hydrate', [$data]]);
    }

    public function toArray()
    {
        return call_user_func_array($this->closure, ['toArray']);
    }
}