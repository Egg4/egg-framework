<?php

namespace Egg\Interfaces;

interface FactoryInterface
{
    public function create(array $data = []);
}