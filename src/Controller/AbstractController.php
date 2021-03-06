<?php

namespace Egg\Controller;

use Egg\Interfaces\ControllerInterface;

abstract class AbstractController implements ControllerInterface
{
    protected $settings;

    public function __construct(array $settings = [])
    {
        $this->settings = array_merge([
            'container' => null,
        ], $settings);
    }

    public function execute($action, array $arguments = [])
    {
        return call_user_func_array([$this, $this->getMethod($action)], $arguments);
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