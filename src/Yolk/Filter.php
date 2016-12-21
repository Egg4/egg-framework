<?php

namespace Egg\Yolk;

abstract class Filter
{
    public static function integer(&$value)
    {
        $result = filter_var($value, FILTER_VALIDATE_INT);
        if ($result !== false) {
            $value = $result;
            return true;
        }

        return false;
    }

    public static function float(&$value)
    {
        $result = filter_var($value, FILTER_VALIDATE_FLOAT);
        if ($result !== false) {
            $value = $result;
            return true;
        }

        return false;
    }

    public static function boolean(&$value)
    {
        $result = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($result !== null) {
            $value = $result;
            return true;
        }

        return false;
    }

    public static function null(&$value)
    {
        if ($value === 'null' OR $value === 'NULL' OR $value === null) {
            $value = null;
            return true;
        }

        return false;
    }
}