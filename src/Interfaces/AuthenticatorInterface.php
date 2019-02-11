<?php

namespace Egg\Interfaces;

interface AuthenticatorInterface
{
    public function create(array $data);
    public function get($key);
}