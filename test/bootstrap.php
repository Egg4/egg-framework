<?php

define('ROOT_DIR', dirname(__DIR__));

include_once(ROOT_DIR . '/vendor/autoload.php');

spl_autoload_register(function($class) {
    if (strpos($class, 'Egg\\') === 0) {
        $dir = strcasecmp(substr($class, -4), 'Test') ? 'src' : 'test';
        $name = substr($class, strlen('Egg'));
        $path = ROOT_DIR . DIRECTORY_SEPARATOR . $dir . strtr($name, '\\', DIRECTORY_SEPARATOR) . '.php';
        if (file_exists($path)) require $path;
    }
});