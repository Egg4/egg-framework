<?php

namespace Egg\Cache;

class Memory extends AbstractCache
{
    protected $cache = [];

    public function __construct(array $settings = [])
    {
        parent::__construct(array_merge([
            'ttl'           => 3600,
            'namespace'     => '',
        ], $settings));
    }

    public function get($key)
    {
        $key = $this->buildKey($key);

        if (!isset($this->cache[$key])) {
            return false;
        }
        $content = unserialize($this->cache[$key]);
        if (time() > $content['timeout']) {
            unset($this->cache[$key]);
            return false;
        }

        return $content['data'];
    }

    public function set($key, $data, $ttl = null)
    {
        $key = $this->buildKey($key);
        $ttl = intval(is_null($ttl) ? $this->settings['ttl'] : $ttl);

        $content = [
            'timeout'   => time() + $ttl,
            'data'      => $data,
        ];
        $this->cache[$key] = serialize($content);
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
        unset($this->cache[$key]);
    }

    public function clear()
    {
        $this->cache = [];
    }
}