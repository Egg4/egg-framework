<?php

namespace Egg\Logger;

class File extends AbstractLogger
{


    public function __construct(array $settings = [])
    {
        parent::__construct(array_merge([
            'filename' => null,
            'dateFormat' => DATE_RFC2822,
        ], $settings));
    }

    public function log($type, $message)
    {
        $message = sprintf("[%s]\t%s\t%s",
            date($this->settings['dateFormat']),
            $type,
            $message . PHP_EOL
        );

        error_log($message, 3, $this->settings['filename']);
    }
}