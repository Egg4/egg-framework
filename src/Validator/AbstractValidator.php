<?php

namespace Egg\Validator;

use Egg\Interfaces\ValidatorInterface;

abstract class AbstractValidator implements ValidatorInterface
{
    protected $settings = [];
    protected $container;
    protected $exception;

    public function validate($action, array $arguments = [])
    {
        $this->exception = new \Egg\Http\Exception($this->container['response'], 400);

        call_user_func_array([$this, $this->getMethod($action)], $arguments);

        if ($this->exception->hasErrors()) {
            throw $this->exception;
        }
    }

    protected function getMethod($action)
    {
        $version = $this->container['request']->getAttribute('version');
        if ($version) {
            $method = $version . '_' . $action;
            if (method_exists($this, $method)) {
                return $method;
            }
        }

        return $action;
    }
}