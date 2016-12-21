<?php

namespace Egg\Component;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Egg\Interfaces\ComponentInterface;
use \Egg\Component\Closure as ClosureComponent;

abstract class AbstractComponent implements ComponentInterface
{
    protected $dependencies = [];
    protected $settings = [];
    protected $container;

    public final function getDependencies()
    {
        return $this->dependencies;
    }

    public final function getSettings()
    {
        return $this->settings;
    }

    public final function setContainer($container)
    {
        $this->container = $container;
    }

    public function init()
    {

    }

    public function __invoke(Request $request, Response $response, callable $next = null)
    {
        if (!$next) {
            $next = function($request, $response) {
                return $response;
            };
        }
        if ($next instanceof \Closure) {
            $next = new ClosureComponent($next);
        }

        return $this->run($request, $response, $next);
    }
}