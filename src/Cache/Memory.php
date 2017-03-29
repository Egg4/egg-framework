<?php

namespace Egg\Cache;

class Memory extends AbstractCache
{
    protected $settings;
    protected $cache;

    public function __construct(array $settings = [])
    {
        $this->settings = array_merge([
            'ttl'           => 3600,
            'namespace'     => '',
        ], $settings);

        $this->cache = new \Egg\Yolk\Shm\Cache();
    }

    protected function buildKey($key)
    {
        return empty($this->settings['namespace']) ? $key : $this->settings['namespace'] . '.' . $key;
    }

    public function get($key)
    {
        $key = $this->buildKey($key);
        return $this->cache->get($key);
    }

    public function set($key, $data, $ttl = null)
    {
        $key = $this->buildKey($key);
        $ttl = $ttl === null ? $this->settings['ttl'] : $ttl;
        $this->cache->set($key, $data, $ttl);
    }

    public function defer($key, $ttl = null)
    {
        $data = $this->get($key);
        if (!$data) {
            throw new \Exception(sprintf('Cache key "%s" not found', $key));
        }
        $this->set($key, $data, $ttl);
    }

    public function delete($key)
    {
        $key = $this->buildKey($key);
        $this->cache->delete($key);
    }

    public function clear()
    {
        $this->cache->clear();
    }
}