<?php

namespace Egg\Orm\Entity;

class Generic extends AbstractEntity
{
    protected $data;

    public function get($key)
    {
        if (!$this->has($key)) throw new \Exception(sprintf('Key "%s" not set', $key));

        return $this->data[$key];
    }

    public function set($key, $value)
    {
        $this->data[$key] = $value;
    }

    public function has($key)
    {
        return isset($this->data[$key]);
    }

    public function detach($key)
    {
        unset($this->data[$key]);
    }

    public function hydrate(array $data)
    {
        $this->data = $data;
    }

    public function toArray()
    {
        return $this->data;
    }
}