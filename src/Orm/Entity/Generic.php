<?php

namespace Egg\Orm\Entity;

class Generic extends AbstractEntity
{
    protected $_data;

    public function get($key)
    {
        if (!$this->has($key)) throw new \Exception(sprintf('Key "%s" not set', $key));

        return $this->_data[$key];
    }

    public function set($key, $value)
    {
        $this->_data[$key] = $value;
    }

    public function has($key)
    {
        return isset($this->_data[$key]);
    }

    public function detach($key)
    {
        unset($this->_data[$key]);
    }

    public function hydrate(array $data)
    {
        $this->_data = $data;
    }

    public function toArray()
    {
        return $this->_data;
    }
}