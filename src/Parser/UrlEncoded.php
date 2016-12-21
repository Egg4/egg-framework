<?php

namespace Egg\Parser;

class UrlEncoded extends AbstractParser
{
    public function parse($string)
    {
        parse_str($string, $data);

        return $data;
    }
}