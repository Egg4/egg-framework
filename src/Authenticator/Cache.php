<?php

namespace Egg\Authenticator;

class Cache extends AbstractAuthenticator
{
    protected $container;
    protected $cache;

    public function __construct(array $settings = [])
    {
        parent::__construct(array_merge([
            'container'     => null,
            'namespace'     => 'authentication',
            'key.length'    => 32,
        ], $settings));

        $this->container = $this->settings['container'];
        $this->cache = $this->container['cache'];
    }

    protected function buildKey($key)
    {
        return empty($this->settings['namespace']) ? $key : $this->settings['namespace'] . '.' . $key;
    }

    public function create(array $data)
    {
        $key = \Egg\Yolk\Rand::alphanum($this->settings['key.length']);
        $data['key'] = $key;
        $key = $this->buildKey($key);
        $this->cache->set($key, $data);

        return $data;
    }

    public function get($key)
    {
        $key = $this->buildKey($key);
        return $this->cache->get($key);
    }

    public function set($key, array $data)
    {
        $key = $this->buildKey($key);
        $this->cache->set($key, $data);
    }

    public function delete($key)
    {
        $key = $this->buildKey($key);
        $this->cache->delete($key);
    }
}