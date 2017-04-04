<?php

namespace Egg\Interfaces;

interface AuthenticatorInterface
{
    public function register(array $data);
    public function unregister($key);
    public function authenticate($key);
}