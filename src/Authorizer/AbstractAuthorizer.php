<?php

namespace Egg\Authorizer;

use Egg\Interfaces\AuthorizerInterface;

abstract class AbstractAuthorizer implements AuthorizerInterface
{
    protected $container;

    public final function setContainer($container)
    {
        $this->container = $container;
    }

    public function init()
    {

    }

    public function authorize($action, array $arguments = [])
    {
        return call_user_func_array([$this, $action], $arguments);
    }
}