<?php

namespace Egg\Logger;

use Egg\Interfaces\LoggerInterface;

abstract class AbstractLogger implements LoggerInterface
{
    const TYPE_ERROR = 'error';
    const TYPE_WARN = 'warn';
    const TYPE_INFO = 'info';

    protected $settings;

    public function __construct(array $settings = [])
    {
        $this->settings = array_merge([

        ], $settings);
    }

    public function error($message)
    {
        $this->log(AbstractLogger::TYPE_ERROR, $message);
    }

    public function warn($message)
    {
        $this->log(AbstractLogger::TYPE_WARN, $message);
    }

    public function info($message)
    {
        $this->log(AbstractLogger::TYPE_INFO, $message);
    }
}