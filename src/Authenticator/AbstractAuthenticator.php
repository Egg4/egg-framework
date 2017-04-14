<?php

namespace Egg\Authenticator;

use Egg\Interfaces\AuthenticatorInterface;

abstract class AbstractAuthenticator implements AuthenticatorInterface
{
    protected $settings;

    public function __construct(array $settings = [])
    {
        $this->settings = array_merge([
            'container' => null,
        ], $settings);
    }
}