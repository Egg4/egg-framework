<?php

namespace Egg\Orm\Schema;

use Egg\Interfaces\SchemaInterface;

abstract class AbstractSchema implements SchemaInterface
{
    protected $settings = [];
}