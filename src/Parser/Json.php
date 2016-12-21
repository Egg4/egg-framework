<?php

namespace Egg\Parser;

class Json extends AbstractParser
{
    public function __construct(array $settings = [])
    {
        if (!function_exists('json_decode')) {
            throw new \Exception('Function "json_decode" not found');
        }

        $this->settings = array_merge(array(
            'assoc' => true,
        ), $settings);
    }

    public function parse($string)
    {
        $array = json_decode($string, $this->settings['assoc']);
        switch(json_last_error()) {
            case JSON_ERROR_DEPTH:
                throw new \Exception('Json error: Maximum stack depth exceeded');
            case JSON_ERROR_CTRL_CHAR:
                throw new \Exception('Json error: Unexpected control character found');
            case JSON_ERROR_SYNTAX:
                throw new \Exception('Json error: Syntax error');
        }

        return $array;
    }
}