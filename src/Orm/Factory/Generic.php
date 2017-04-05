<?php

namespace Egg\Orm\Factory;

class Generic extends AbstractFactory
{
    public function __construct(array $settings = [])
    {
        $this->settings = array_merge([
            'schema'        => null,
            'table'         => null,
            'repository'    => null,
        ], $settings);
    }

    public function create(array $data = [])
    {
        $schema = $this->settings['schema']->getData();
        $columns = $schema['tables'][$this->settings['table']]['columns'];
        $data = $this->generateAttributes($columns, $data);

        $id = $this->settings['repository']->insert($data);
        $entity = $this->settings['repository']->selectOneById($id);

        return $entity;
    }

    protected function generateAttributes($columns, $data)
    {
        $attributes = [];

        foreach($columns as $name => $column) {
            if (isset($data[$name])) {
                $attributes[$name] = $data[$name];
                continue;
            }
            if ($column['auto.increment']) {
                continue;
            }
            if ($column['default']) {
                $attributes[$name] = $column['default'];
                continue;
            }
            $attributes[$name] = $this->generateAttribute($column);
        }

        return $attributes;
    }

    protected function generateAttribute($column)
    {
        switch ($column['type']) {
            case 'integer':
                $min = $column['unsigned'] ? 0 : PHP_INT_MIN;
                return \Egg\Yolk\Rand::integer($min, PHP_INT_MAX);
            case 'string':
                return \Egg\Yolk\Rand::alphanum(min(32, $column['max.length']));
            case 'boolean':
                return \Egg\Yolk\Rand::boolean() ? 1 : 0;
            case 'float':
                return \Egg\Yolk\Rand::float();
            default:
                return null;
        }
    }
}