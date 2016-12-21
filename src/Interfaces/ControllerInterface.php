<?php

namespace Egg\Interfaces;

interface ControllerInterface
{
    public function execute($action, array $arguments = []);
}