<?php

namespace Egg\Resolver;

class Pattern extends AbstractResolver
{
    protected $pattern;

    public function __construct($pattern)
    {
        $this->pattern = $pattern;
    }

    public function resolve()
    {
        list($params) = func_get_args();

        $replacements = [];
        foreach($params as $key => $value) {
            $replacements[sprintf('{%s}', $key)] = ucfirst(\Egg\Yolk\String::camelize($value));
        }
        $class = str_replace(array_keys($replacements), array_values($replacements), $this->pattern);

        return $class;
    }
}