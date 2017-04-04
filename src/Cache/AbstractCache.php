<?php

namespace Egg\Cache;

use Egg\Interfaces\CacheInterface;

abstract class AbstractCache implements CacheInterface
{
    protected $settings = [];

    protected function buildKey($key)
    {
        return empty($this->settings['namespace']) ? $key : $this->settings['namespace'] . '.' . $key;
    }
}