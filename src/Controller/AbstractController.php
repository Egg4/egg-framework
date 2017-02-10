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
        $version = $this->container['request']->getAttribute('version');
        if ($version) {
            $method = $version . '_' . $action;
            if (method_exists($this, $method)) {
                return call_user_func_array([$this, $method], $arguments);
            }
        }

        return call_user_func_array([$this, $action], $arguments);
    }
}