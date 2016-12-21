<?php

namespace Egg\Formatter;

class UrlEncoded extends AbstractFormatter
{
    protected $settings;

    public function __construct(array $settings = [])
    {
        $this->settings = array_merge(array(
            'numeric_prefix' => null,
            'arg_separator' => null,
            'enc_type' => PHP_QUERY_RFC1738,
        ), $settings);
    }

    public function format(array $array)
    {
        return http_build_query(
            $array,
            $this->settings['numeric_prefix'],
            $this->settings['arg_separator'],
            $this->settings['enc_type']
        );
    }
}