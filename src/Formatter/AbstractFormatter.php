<?php

namespace Egg\Formatter;

use Egg\Interfaces\FormatterInterface;

abstract class AbstractFormatter implements FormatterInterface
{
    protected $settings = [];
}