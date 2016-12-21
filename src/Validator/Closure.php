<?php

namespace Egg\Validator;

class Closure extends AbstractValidator
{
    use \Egg\Yolk\ClosureAwareTrait;

    public function __construct(\Closure $closure)
    {
        $this->closure = $closure;
    }

    public function validate($action, array $arguments = [])
    {
        return call_user_func_array($this->closure, [$action, $arguments]);
    }
}