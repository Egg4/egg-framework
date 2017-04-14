<?php

namespace Egg\Authorizer;

use Egg\Interfaces\AuthorizerInterface;

abstract class AbstractAuthorizer implements AuthorizerInterface
{
    protected $settings;

    public function __construct(array $settings = [])
    {
        $this->settings = array_merge([
            'container' => null,
        ], $settings);
    }

    public function authorize($action, array $arguments = [])
    {
        return call_user_func_array([$this, $action], $arguments);
    }
}