<?php

namespace Egg\Cache;

class Memcache extends AbstractCache
{
    protected $memcache;

    public function __construct(array $settings = [])
    {
        parent::__construct(array_merge([
            'host'          => 'localhost',
            'port'          => 11211,
            'timeout'       => 1,
            'ttl'           => 3600,
            'namespace'     => '',
            'compress'      => false,
        ], $settings));

        $this->memcache = new \Memcache();
        $this->memcache->connect(
            $this->settings['host'],
            $this->settings['port'],
            $this->settings['timeout']
        );
    }

    public function get($key)
    {
        $key = $this->buildKey($key);
        return $this->memcache->get($key);
    }

    public function set($key, $data, $ttl = null)
    {
        $key = $this->buildKey($key);
        $compress = $this->settings['compress'] AND !is_bool($data) AND !is_numeric($data);
        $flag = $compress ? MEMCACHE_COMPRESSED : false;
        $ttl = $ttl === null ? $this->settings['ttl'] : $ttl;
        $this->memcache->set($key, $data, $flag, $ttl);
    }

    public function defer($key, $ttl = null)
    {
        $data = $this->get($key);
        if (!$data) {
            throw new \Exception(sprintf('Memcache key "%s" not found', $key));
        }
        $this->set($key, $data, $ttl);
    }

    public function delete($key)
    {
        $key = $this->buildKey($key);
        $this->memcache->delete($key);
    }

    public function clear()
    {
        $this->memcache->flush();
    }
}