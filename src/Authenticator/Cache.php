<?php

namespace Egg\Authenticator;

class Cache extends AbstractAuthenticator
{
    protected $cache;

    public function __construct(array $settings = [])
    {
        parent::__construct(array_merge([
            'cache'         => null,
            'namespace'     => 'authentication',
            'key.length'    => 32,
        ], $settings));

        if (is_null($this->settings['cache'])) {
            throw new \Exception('Cache not set');
        }
        else {
            $this->cache = $this->settings['cache'];
        }
    }

    protected function buildKey($key)
    {
        return empty($this->settings['namespace']) ? $key : $this->settings['namespace'] . '.' . $key;
    }

    public function create(array $data)
    {
        $key = \Egg\Yolk\Rand::alphanum($this->settings['key.length']);
        $this->cache->set($this->buildKey($key), $data, $this->settings['timeout']);

        return $key;
    }

    public function get($key)
    {
        $key = $this->buildKey($key);
        $data = $this->cache->get($key);
        if ($data) {
            $this->cache->defer($key, $this->settings['timeout']);
        }

        return $data;
    }
}