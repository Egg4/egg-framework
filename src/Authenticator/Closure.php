<?php

namespace Egg\Authenticator;

class Closure extends AbstractAuthenticator
{
    use \Egg\Yolk\ClosureAwareTrait;

    public function __construct(\Closure $closure)
    {
        $this->closure = $closure;
    }

    public function create(array $data)
    {
        return call_user_func_array($this->closure, ['create', [$data]]);
    }

    public function get($key)
    {
        return call_user_func_array($this->closure, ['get', [$key]]);
    }

    public function set($key, array $data)
    {
        return call_user_func_array($this->closure, ['set', [$key, $data]]);
    }

    public function delete($key)
    {
        return call_user_func_array($this->closure, ['delete', [$key]]);
    }
}