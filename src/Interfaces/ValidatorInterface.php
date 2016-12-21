<?php

namespace Egg\Interfaces;

interface ValidatorInterface
{
    public function validate($action, array $arguments = []);
}