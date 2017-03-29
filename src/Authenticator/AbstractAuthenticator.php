<?php

namespace Egg\Authenticator;

use Egg\Interfaces\AuthenticatorInterface;

abstract class AbstractAuthenticator implements AuthenticatorInterface
{
    protected $container;

    public final function setContainer($container)
    {
        $this->container = $container;
    }

    public function init()
    {

    }
}