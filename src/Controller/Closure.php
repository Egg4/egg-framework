<?php

namespace Egg\Controller;

class Closure extends AbstractController
{
    use \Egg\Yolk\ClosureAwareTrait;

    public function __construct(\Closure $closure)
    {
        $this->closure = $closure;
    }

    public function execute($action, array $arguments = [])
    {
        return call_user_func_array($this->closure, [$action, $arguments]);
    }
}