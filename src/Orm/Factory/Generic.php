<?php

namespace Egg\Orm\Factory;

class Generic extends AbstractFactory
{
    protected $container;
    protected $resource;
    protected $schema;

    public function __construct(array $settings = [])
    {
        parent::__construct(array_merge([
            'container' => null,
            'resource'  => null,
        ], $settings));

        $this->container = $this->settings['container'];
        $this->resource = $this->settings['resource'];
        $this->schema = $this->container['schema'];
    }

    public function create(array $data = [])
    {
        $schema = $this->schema->getData();
        $columns = $schema->tables[$this->resource]->columns;
        $data = $this->createAttributes($columns, $data);

        $repository = $this->container['repository'][$this->resource];
        $id = $repository->insert($data);
        $entity = $repository->selectOneById($id);

        return $entity;
    }

    protected function createAttributes($columns, $data)
    {
        $attributes = [];

        foreach($columns as $name => $column) {
            $method = 'create' . ucfirst(\Egg\Yolk\String::camelize($column->name));
            if (method_exists($this, $method)) {
                $value = isset($data[$name]) ? $data[$name] : null;
                $attributes[$name] = call_user_func([$this, $method], $value);
                continue;
            }
            if (isset($data[$name])) {
                $attributes[$name] = $data[$name];
                continue;
            }
            if ($column->auto_increment) {
                continue;
            }
            $attributes[$name] = $this->createAttribute($column);
        }

        return $attributes;
    }

    protected function createAttribute($column)
    {
        if ($column->foreign_key !== null) {
            $resource = $column->foreign_key->foreign_column->table->name;
            $factory = $this->container['factory'][$resource];
            $entity = $factory->create();
            return $entity->id;
        }

        if ($column->default !== null) {
            return $column->default;
        }

        switch ($column->type) {
            case 'integer':
                $min = $column->unsigned ? 0 : PHP_INT_MIN;
                return \Egg\Yolk\Rand::integer($min, PHP_INT_MAX);
            case 'string':
                return \Egg\Yolk\Rand::alphanum(min(32, $column->max_length));
            case 'boolean':
                return \Egg\Yolk\Rand::boolean() ? 1 : 0;
            case 'float':
                return \Egg\Yolk\Rand::float();
            default:
                return null;
        }
    }
}