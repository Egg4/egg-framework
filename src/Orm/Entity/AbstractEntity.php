<?php

namespace Egg\Orm\Entity;

use Egg\Interfaces\EntityInterface;

abstract class AbstractEntity implements EntityInterface
{
    public function __get($key)
    {
        return $this->get($key);
    }

    public function __set($key, $value)
    {
        $this->set($key, $value);
    }

    public function __isset($key)
    {
        return $this->has($key);
    }

    public function __unset($key)
    {
        $this->detach($key);
    }
}