<?php

namespace Egg\Interfaces;

interface AuthenticatorInterface
{
    public function register($data);
    public function unregister($key);
    public function authenticate($key);
}