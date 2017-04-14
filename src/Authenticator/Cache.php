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

    public function register(array $data)
    {
        $id = \Egg\Yolk\Rand::alphanum($this->settings['key.length']);
        $data['key'] = $id;
        $key = $this->buildKey($id);
        $this->cache->set($key, $data);

        return $data;
    }

    public function unregister($key)
    {
        $key = $this->buildKey($key);
        $this->cache->delete($key);
    }

    public function authenticate($key)
    {
        $key = $this->buildKey($key);
        return $this->cache->get($key);
    }
}