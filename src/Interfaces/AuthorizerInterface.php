<?php

namespace Egg\Interfaces;

interface AuthorizerInterface
{
    public function authorize($action, array $arguments = []);
}