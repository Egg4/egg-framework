<?php

namespace Egg\Yolk;

abstract class String
{
    public static function camelize($string)
    {
        return lcfirst(implode('', array_map('ucfirst', array_map('strtolower', preg_split('/[\s,\-_]+/', $string)))));
    }

    public static function underscore($string)
    {
        return implode('_', array_map('strtolower', preg_split('/([A-Z]{1}[^A-Z]*)/', $string, -1, PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY)));
    }

    public static function slugify($string, $pattern = '~[^\\pL\d]+~u', $replace = '')
    {
        return preg_replace('~[^-\w]+~', '', iconv('utf-8', 'us-ascii//TRANSLIT', trim(preg_replace($pattern, $replace, $string), '-')));
    }
}