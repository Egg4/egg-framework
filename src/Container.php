<?php

namespace Egg;

use Interop\Container\ContainerInterface;
use Pimple\Container as PimpleContainer;

class Container extends PimpleContainer implements ContainerInterface
{
    protected $callable;

    public function __construct($input)
    {
        if (is_callable($input)) {
            $this->callable = $input;
            parent::__construct();
        }
        else {
            parent::__construct($input);
        }
    }

    public function offsetGet($id)
    {
        if (!$this->offsetExists($id) AND $this->callable) {
            $value = call_user_func_array($this->callable, [$this, $id]);
            $this->offsetSet($id, $value);
        }

        return parent::offsetGet($id);
    }

    public function get($id)
    {
        return $this->offsetGet($id);
    }

    public function has($id)
    {
        return $this->offsetExists($id);
    }

    public function __get($id)
    {
        return $this->get($id);
    }

    public function __isset($id)
    {
        return $this->has($id);
    }
}