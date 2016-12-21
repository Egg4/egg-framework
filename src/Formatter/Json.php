<?php

namespace Egg\Formatter;

class Json extends AbstractFormatter
{
    public function __construct()
    {
        if (!function_exists('json_encode')) {
            throw new \Exception('Function "json_encode" not found');
        }
    }

    public function format(array $array)
    {
        return json_encode($array);
    }
}