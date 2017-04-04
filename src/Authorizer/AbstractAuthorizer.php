<?php

namespace Egg\Authorizer;

use Egg\Interfaces\AuthorizerInterface;

abstract class AbstractAuthorizer implements AuthorizerInterface
{
    protected $settings = [];
    protected $container;

    public function authorize($action, array $arguments = [])
    {
        return call_user_func_array([$this, $action], $arguments);
    }
}