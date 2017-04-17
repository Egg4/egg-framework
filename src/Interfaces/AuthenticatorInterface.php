<?php

namespace Egg\Interfaces;

interface AuthenticatorInterface
{
    public function create(array $data);
    public function get($key);
    public function set($key, array $data);
    public function delete($key);
}