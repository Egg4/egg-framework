<?php

namespace Egg\Controller;

use Egg\Interfaces\ControllerInterface;

abstract class AbstractController implements ControllerInterface
{
    protected $container;

    public final function setContainer($container)
    {
        $this->container = $container;
    }

    public function init()
    {

    }

    public function execute($action, array $arguments = [])
    {
        return call_user_func_array([$this, $action], $arguments);
    }
}