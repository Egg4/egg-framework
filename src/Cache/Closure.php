<?php

namespace Egg\Cache;

class Closure extends AbstractCache
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

    public function set($key, $data, $ttl = null)
    {
        return call_user_func_array($this->closure, ['set', [$key, $data, $ttl]]);
    }

    public function defer($key, $ttl = null)
    {
        return call_user_func_array($this->closure, ['defer', [$key, $ttl]]);
    }

    public function delete($key)
    {
        return call_user_func_array($this->closure, ['delete', [$key]]);
    }

    public function clear()
    {
        return call_user_func_array($this->closure, ['delete']);
    }
}