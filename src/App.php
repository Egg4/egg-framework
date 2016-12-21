<?php

namespace Egg;

use Egg\Interfaces\ServiceInterface as Service;
use Egg\Interfaces\ComponentInterface as Component;
use Egg\Component\Collection as ComponentCollection;

class App
{
    protected $slimApp;
    protected $components;

    public function __construct($container = [])
    {
        $container['settings']['determineRouteBeforeAppMiddleware'] = true;

        $this->slimApp = new \Slim\App($container);
        $this->components = new ComponentCollection();
    }

    public function service(Service $service)
    {

    }

    public function component(Component $component)
    {
        $this->components->set(get_class($component), $component);
    }

    public function run($silent = false)
    {
        $middlewareStack = array_reverse($this->components->stack());
        foreach($middlewareStack as $middleware) {
            $this->slimApp->add($middleware);
        }

        return $this->slimApp->run($silent);
    }
}