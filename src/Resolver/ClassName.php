<?php

namespace Egg\Resolver;

class ClassName extends AbstractResolver
{
    protected $settings = [];

    public function __construct(array $settings = [])
    {
        $this->settings = array_merge([
            'search'    => '',
            'fallback'  => '',
        ], $settings);
    }

    public function resolve()
    {
        list($params) = func_get_args();

        $replacements = [];
        foreach($params as $key => $value) {
            $replacements[sprintf('{%s}', $key)] = ucfirst(\Egg\Yolk\String::camelize($value));
        }

        $className = str_replace(
            array_keys($replacements),
            array_values($replacements),
            $this->settings['search']
        );

        if (!class_exists($className)) {
            $className = str_replace(
                array_keys($replacements),
                array_values($replacements),
                $this->settings['fallback']
            );
        }

        return $className;
    }
}