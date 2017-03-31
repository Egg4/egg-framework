<?php

namespace Egg\Authenticator;

class Cache extends AbstractAuthenticator
{
    protected $cache;

    public function init()
    {
        $this->cache = $this->container['cache'];
    }

    protected function buildKey($key)
    {
        return 'authentication.' . $key;
    }

    public function register($data)
    {
        $id = md5(mt_rand());
        $key = $this->buildKey($id);
        $this->cache->set($key, $data);

        return $id;
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