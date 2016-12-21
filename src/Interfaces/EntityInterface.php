<?php

namespace Egg\Interfaces;

interface EntityInterface
{
    public function get($key);
    public function set($key, $value);
    public function has($key);
    public function detach($key);
    public function hydrate(array $data);
    public function toArray();
}