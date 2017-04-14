<?php

namespace Egg\Orm\Factory;

use Egg\Interfaces\FactoryInterface;

abstract class AbstractFactory implements FactoryInterface
{
    protected $settings;

    public function __construct(array $settings = [])
    {
        $this->settings = array_merge([
            'container'         => null,
        ], $settings);
    }
}