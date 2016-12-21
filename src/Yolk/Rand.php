<?php

namespace Egg\Yolk;

abstract class Rand
{
    const CHAR_NUM = 1;
    const CHAR_ALPHA_LOWERCASE = 2;
    const CHAR_ALPHA_UPPERCASE = 4;

    public static function bytes($length, $strong = false)
    {
        if ($length <= 0) {
            return false;
        }
        if (extension_loaded('openssl')) {
            $rand = openssl_random_pseudo_bytes($length, $secure);
            if ($secure === true) {
                return $rand;
            }
        }
        if (extension_loaded('mcrypt')) {
            // PHP bug #55169
            // @see https://bugs.php.net/bug.php?id=55169
            if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN' ||
                version_compare(PHP_VERSION, '5.3.7') >= 0) {
                $rand = mcrypt_create_iv($length, MCRYPT_DEV_URANDOM);
                if ($rand !== false && strlen($rand) === $length) {
                    return $rand;
                }
            }
        }
        if ($strong) {
            throw new \Exception(
                'This PHP environment doesn\'t support secure random number generation. ' .
                'Please consider to install the OpenSSL and/or Mcrypt extensions'
            );
        }
        $rand = '';
        for ($i = 0; $i < $length; $i++) {
            $rand .= chr(mt_rand(0, 255));
        }
        return $rand;
    }

    public static function boolean($strong = false)
    {
        $byte = static::bytes(1, $strong);
        return (boolean) (ord($byte) % 2);
    }

    public static function integer($min, $max, $strong = false)
    {
        if ($min > $max) {
            throw new \Exception('The min parameter must be lower than max parameter');
        }
        $range = $max - $min;
        if ($range == 0) {
            return $max;
        } elseif ($range > PHP_INT_MAX || is_float($range)) {
            throw new \Exception('The supplied range is too great to generate');
        }
        $log    = log($range, 2);
        $bytes  = (int) ($log / 8) + 1;
        $bits   = (int) $log + 1;
        $filter = (int) (1 << $bits) - 1;
        do {
            $rnd = hexdec(bin2hex(static::bytes($bytes, $strong)));
            $rnd = $rnd & $filter;
        } while ($rnd > $range);

        return ($min + $rnd);
    }

    public static function float($strong = false)
    {
        $bytes    = static::bytes(7, $strong);
        $bytes[6] = $bytes[6] | chr(0xF0);
        $bytes   .= chr(63); // exponent bias (1023)
        list(, $float) = unpack('d', $bytes);

        return ($float - 1);
    }

    public static function string($length, $charlist = null, $strong = false)
    {
        if ($length < 1) {
            throw new \Exception('Length should be >= 1');
        }

        if (empty($charlist)) {
            $numBytes = ceil($length * 0.75);
            $bytes    = static::bytes($numBytes);
            return substr(rtrim(base64_encode($bytes), '='), 0, $length);
        }

        $listLen = strlen($charlist);
        if ($listLen == 1) {
            return str_repeat($charlist, $length);
        }

        $bytes  = static::bytes($length, $strong);
        $pos    = 0;
        $result = '';
        for ($i = 0; $i < $length; $i++) {
            $pos     = ($pos + ord($bytes[$i])) % $listLen;
            $result .= $charlist[$pos];
        }

        return $result;
    }

    public static function alphanum(
        $length,
        $flag = false,
        $strong = false
    ) {
        $flag = $flag ? $flag : self::CHAR_NUM | self::CHAR_ALPHA_LOWERCASE | self::CHAR_ALPHA_UPPERCASE;

        $charlist = '';
        if ($flag % 2) $charlist .= '0123456789';
        if (($flag >> 1) % 2) $charlist .= 'abcdefghijklmnopqrstuvwxyz';
        if (($flag >> 2) % 2) $charlist .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

        return static::string($length, $charlist, $strong);
    }

    public static function alpha($length, $strong = false)
    {
        return static::alphanum($length, self::CHAR_ALPHA_LOWERCASE | self::CHAR_ALPHA_UPPERCASE, $strong);
    }

    public static function numeric($length, $strong = false)
    {
        return static::alphanum($length, self::CHAR_NUM, $strong);
    }

    public static function alphaLower($length, $strong = false)
    {
        return static::alphanum($length, self::CHAR_ALPHA_LOWERCASE, $strong);
    }

    public static function alphaUpper($length, $strong = false)
    {
        return static::alphanum($length, self::CHAR_ALPHA_UPPERCASE, $strong);
    }
}