<?php

namespace Egg\Interfaces;

interface CacheInterface
{
    public function get($key);
    public function set($key, $data, $ttl = null);
    public function defer($key, $ttl = null);
    public function delete($key);
    public function clear();
}