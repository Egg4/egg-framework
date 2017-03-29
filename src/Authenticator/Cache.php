<?php

namespace Egg\Authenticator;

class Cache extends AbstractAuthenticator
{
    protected $cache;

    public function init()
    {
        $this->cache = $this->container['cache'];
    }

    public function register($data)
    {
        $key = md5(mt_rand());
        $this->cache->set($key, $data);

        return $key;
    }

    public function unregister($key)
    {
        $this->cache->delete($key);
    }

    public function authenticate($key)
    {
        return $this->cache->get($key);
    }
}