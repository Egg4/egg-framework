<?php

namespace Egg\Validator;

class Generic extends AbstractValidator
{
    protected $resource;

    public function __construct(array $settings = [])
    {
        $this->settings = array_merge([
            'container' => null,
            'resource'  => null,
        ], $settings);
        $this->container = $this->settings['container'];
        $this->resource = $this->settings['resource'];
    }

    public function select(array $filterParams, array $sortParams, array $rangeParams)
    {

    }

    public function search(array $filterParams, array $sortParams, array $rangeParams)
    {

    }

    public function read($id)
    {
        $this->checkEntityExists($id);
    }

    public function create(array $params)
    {
        $this->checkParams($params);
    }

    public function replace($id, array $params)
    {
        $this->checkParams($params);
        $this->checkEntityExists($id);
    }

    public function update($id, array $params)
    {
        $this->checkParams($params);
        $this->checkEntityExists($id);
    }

    public function delete($id)
    {
        $this->checkEntityExists($id);
    }

    public function __call($action, $arguments)
    {

    }

    protected function checkEntityExists($id)
    {
        $entity = $this->container['repository'][$this->resource]->selectOneById($id);
        if (!$entity) {
            $this->exception->addError(new \Egg\Http\Error(array(
                'name'          => 'not_found',
                'description'   => sprintf('"%s %s" not found', $this->resource, $id),
            )));
        }
    }

    protected function checkParams(array $params)
    {
        $schema = $this->container['schema']->getData();
        $table = $schema->tables[$this->resource];

        foreach($table->columns as $column) {
            if (!$column->nullable AND is_null($column->default) AND !$column->auto_increment) {
                $this->requireParam($column->name, $params);
            }
            if (!isset($params[$column->name])) {
                continue;
            }
            $this->checkParam($params[$column->name], $column);
        }

        foreach($table->unique_keys as $uniqueKey) {
            $keys = array_keys($uniqueKey->columns);
            if (count(array_diff($keys, array_keys($params))) == 0) {
                $uniqueParams = array_intersect_key($params, $keys);
                $this->checkUnique($uniqueParams);
            }
        }
    }

    protected function checkParam($param, $column)
    {
        if (!$column->nullable) {
            $this->checkParamNotNull($param, $column);
        }
        $this->checkParamType($param, $column);
        if ($column->foreign_key) {
            $this->checkForeignKey($param, $column->foreign_key);
        }
    }

    protected function checkParamNotNull($param, $column)
    {
        if (is_null($param)) {
            $this->exception->addError(new \Egg\Http\Error(array(
                'name'          => 'invalid_content',
                'description'   => sprintf('Param "%s" is null', $column->name),
            )));
        }
    }

    protected function checkParamType($param, $column)
    {
        switch ($column->type) {
            case 'integer':
                return $this->checkParamTypeInteger($param, $column);
            case 'string':
                return $this->checkParamTypeString($param, $column);
            case 'boolean':
                return $this->checkParamTypeBool($param, $column);
            case 'float':
                return $this->checkParamTypeFloat($param, $column);
        }
    }

    protected function checkParamTypeInteger($param, $column)
    {
        if (!is_numeric($param)) {
            $this->exception->addError(new \Egg\Http\Error(array(
                'name'          => 'invalid_content',
                'description'   => sprintf('Param "%s" integer expected', $column->name),
            )));
        }
        if ($column->unsigned) {
            $this->checkParamUnsigned($param, $column);
        }
    }

    protected function checkParamTypeString($param, $column)
    {
        if (!is_string($param)) {
            $this->exception->addError(new \Egg\Http\Error(array(
                'name'          => 'invalid_content',
                'description'   => sprintf('Param "%s" string expected', $column->name),
            )));
        }
        if ($column->max_length > 0) {
            $this->checkParamMaxLength($param, $column);
        }
    }

    protected function checkParamTypeBool($param, $column)
    {
        if (!is_bool($param)) {
            $this->exception->addError(new \Egg\Http\Error(array(
                'name'          => 'invalid_content',
                'description'   => sprintf('Param "%s" boolean expected', $column->name),
            )));
        }
    }

    protected function checkParamTypeFloat($param, $column)
    {
        if (!is_numeric($param)) {
            $this->exception->addError(new \Egg\Http\Error(array(
                'name'          => 'invalid_content',
                'description'   => sprintf('Param "%s" float expected', $column->name),
            )));
        }
        if ($column->unsigned) {
            $this->checkParamUnsigned($param, $column);
        }
    }

    protected function checkParamUnsigned($param, $column)
    {
        if ($param < 0) {
            $this->exception->addError(new \Egg\Http\Error(array(
                'name'          => 'invalid_content',
                'description'   => sprintf('Param "%s" unsigned expected', $column->name),
            )));
        }
    }

    protected function checkParamMaxLength($param, $column)
    {
        if (strlen($param) > $column->max_length) {
            $this->exception->addError(new \Egg\Http\Error(array(
                'name'          => 'invalid_content',
                'description'   => sprintf('Param "%s" max length "%s"', $column->name, $column->max_length),
            )));
        }
    }

    protected function checkForeignKey($param, $foreignKey)
    {
        $foreignColumn = $foreignKey->foreign_column;
        $entity = $this->container['repository'][$foreignColumn->table]->selectOne([$foreignColumn->name => $param]);
        if (!$entity) {
            $this->exception->addError(new \Egg\Http\Error(array(
                'name'          => 'not_found',
                'description'   => sprintf('"%s %s=%s" not found', $foreignColumn->table, $foreignColumn->name, $param),
            )));
        }
    }

    protected function checkUnique($params)
    {
        $entity = $this->container['repository'][$this->resource]->selectOne($params);
        if ($entity) {
            $this->exception->addError(new \Egg\Http\Error(array(
                'name'          => 'unique_failure',
                'description'   => sprintf('"%s %s" already exists', $this->resource, http_build_query($params, null, ',')),
            )));
        }
    }

    protected function requireParams(array $keys, array $params)
    {
        foreach($keys as $key) {
            $this->requireParam($key, $params);
        }
    }

    protected function requireParam($key, array $params)
    {
        if (!isset($params[$key])) {
            $this->exception->addError(new \Egg\Http\Error(array(
                'name'          => 'invalid_content',
                'description'   => sprintf('Param "%s" is required', $key),
            )));
        }
    }
}