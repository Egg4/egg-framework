<?php

namespace Egg\Cache;

use Egg\Interfaces\CacheInterface;

abstract class AbstractCache implements CacheInterface
{
    protected $settings;

    public function __construct(array $settings = [])
    {
        $this->settings = array_merge([
            'namespace'     => '',
        ], $settings);
    }

    protected function buildKey($key)
    {
        return empty($this->settings['namespace']) ? $key : $this->settings['namespace'] . '.' . $key;
    }
}