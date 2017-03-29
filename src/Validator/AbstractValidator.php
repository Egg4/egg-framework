<?php

namespace Egg\Validator;

use Egg\Interfaces\ValidatorInterface;

abstract class AbstractValidator implements ValidatorInterface
{
    protected $exception;
    protected $container;

    public final function setContainer($container)
    {
        $this->container = $container;
    }

    public function init()
    {

    }

    public function validate($action, array $arguments = [])
    {
        $this->exception = new \Egg\Http\Exception($this->container['response'], 400);
        call_user_func_array([$this, $action], $arguments);
        if ($this->exception->hasErrors()) throw $this->exception;
    }
}