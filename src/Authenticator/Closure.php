<?php

namespace Egg\Authenticator;

class Closure extends AbstractAuthenticator
{
    use \Egg\Yolk\ClosureAwareTrait;

    public function __construct(\Closure $closure)
    {
        $this->closure = $closure;
    }

    public function register(array $data)
    {
        return call_user_func_array($this->closure, ['register', [$data]]);
    }

    public function unregister($key)
    {
        return call_user_func_array($this->closure, ['unregister', [$key]]);
    }

    public function authenticate($key)
    {
        return call_user_func_array($this->closure, ['authenticate', [$key]]);
    }
}