<?php

namespace Egg\Parser;

use Egg\Interfaces\ParserInterface;

abstract class AbstractParser implements ParserInterface
{
    protected $settings = [];
}