<?php

namespace Egg\Authenticator;

class Cache extends AbstractAuthenticator
{
    protected $settings = [];

    public function __construct(array $settings = [])
    {
        $this->settings = array_merge([
            'cache'         => null,
            'namespace'     => 'authentication',
            'key.length'    => 32,
        ], $settings);
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
        $this->settings['cache']->set($key, $data);

        return $data;
    }

    public function unregister($key)
    {
        $key = $this->buildKey($key);
        $this->settings['cache']->delete($key);
    }

    public function authenticate($key)
    {
        $key = $this->buildKey($key);
        return $this->settings['cache']->get($key);
    }
}