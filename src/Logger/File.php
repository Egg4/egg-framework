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
        
        if (is_null($this->settings['filename'])) {
            throw new \Exception('Logger filename not set');
        }
        if (!file_exists($this->settings['filename'])) {
            $directory = dirname($this->settings['filename']);
            if (!file_exists($directory)) {
                mkdir($directory, 0777, true);
            }
            $fd = fopen($this->settings['filename'], 'w');
            fclose($fd);
        }
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