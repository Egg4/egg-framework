<?php

namespace Egg\Orm\Factory;

use Egg\Interfaces\FactoryInterface;

abstract class AbstractFactory implements FactoryInterface
{
    protected $settings = [];
}