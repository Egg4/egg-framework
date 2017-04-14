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

        $className = $this->buildClassName($this->settings['search'], $replacements);
        if (!class_exists($className)) {
            $className = $this->buildClassName($this->settings['fallback'], $replacements);
        }

        return $className;
    }

    protected function buildClassName($pattern, $replacements)
    {
        $className = str_replace(
            array_keys($replacements),
            array_values($replacements),
            $pattern
        );

        $parts = explode('\\', $className);
        $parts = array_map(function($part) {
            return ucfirst($part);
        }, $parts);

        $className = implode('\\', $parts);

        return $className;
    }
}