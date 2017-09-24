<?php

namespace Egg\Validator;

use Egg\Exception\InvalidContent as InvalidContentException;
use Egg\Exception\NotFound as NotFoundException;
use Egg\Exception\NotUnique as NotUniqueException;

class Generic extends AbstractValidator
{
    protected $container;
    protected $resource;

    public function __construct(array $settings = [])
    {
        parent::__construct(array_merge([
            'container' => null,
            'resource'  => null,
        ], $settings));

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
        $this->checkEntityExists($this->resource, ['id' => $id]);
    }

    public function create(array $params)
    {
        $this->checkRequired($params);
        $this->checkParams($params);
        $this->checkForeignKeys($params);
        $this->checkUniqueKeys($params);
    }

    public function replace($id, array $params)
    {
        $this->checkRequired($params);
        $this->checkParams($params);
        $this->checkForeignKeys($params);
        $this->checkUniqueKeys($params, ['id' => $id]);
        $this->checkEntityExists($this->resource, ['id' => $id]);
    }

    public function update($id, array $params)
    {
        $this->checkParams($params);
        $this->checkForeignKeys($params);
        $this->checkUniqueKeys($params, ['id' => $id]);
        $this->checkEntityExists($this->resource, ['id' => $id]);
    }

    public function delete($id)
    {
        $this->checkEntityExists($this->resource, ['id' => $id]);
    }

    public function __call($action, $arguments)
    {
        throw new \Exception(sprintf('Custom validator "%s"::"%s" not found', $this->resource, $action));
    }

    protected function requireParams(array $requiredKeys, array $params)
    {
        foreach($requiredKeys as $requiredKey) {
            try {
                $this->requireParam($requiredKey, $params);
            }
            catch (InvalidContentException $exception) {
                $this->exception->addError(new \Egg\Http\Error(array(
                    'name'          => 'invalid_content',
                    'description'   => $exception->getMessage(),
                )));
            }
        }
        if ($this->exception->hasErrors()) {
            throw $this->exception;
        }
    }

    protected function requireParam($requiredKey, $params)
    {
        if (!in_array($requiredKey, array_keys($params))) {
            throw new InvalidContentException(sprintf('Param "%s" is required', $requiredKey));
        }
    }

    protected function checkRequired(array $params)
    {
        $schema = $this->container['schema']->getData();
        $table = $schema->tables[$this->resource];

        $requiredKeys = [];
        foreach($table->columns as $column) {
            if (!$column->nullable AND is_null($column->default) AND !$column->auto_increment) {
                $requiredKeys[] = $column->name;
            }
        }
        $this->requireParams($requiredKeys, $params);
    }

    protected function checkParams(array $params)
    {
        $schema = $this->container['schema']->getData();
        $table = $schema->tables[$this->resource];

        foreach($params as $key => $value) {
            try {
                $method = 'validate' . ucfirst(\Egg\Yolk\String::camelize($key));
                if (method_exists($this, $method)) {
                    call_user_func([$this, $method], $value);
                    continue;
                }
                if (isset($table->columns[$key])) {
                    $this->checkParam($key, $value, $table->columns[$key]);
                    continue;
                }
            }
            catch (InvalidContentException $exception) {
                $this->exception->addError(new \Egg\Http\Error(array(
                    'name'          => 'invalid_content',
                    'description'   => $exception->getMessage(),
                )));
            }
        }
        if ($this->exception->hasErrors()) {
            throw $this->exception;
        }
    }

    protected function checkParam($key, $value, $column)
    {
        if (!$column->nullable) {
            $this->checkParamNotNull($key, $value);
        }
        if (!is_null($value)) {
            $this->checkParamType($key, $value, $column->type, [
                'unsigned' => $column->unsigned,
                'maxLength' => $column->max_length,
            ]);
        }
    }

    protected function checkForeignKeys(array $params)
    {
        $schema = $this->container['schema']->getData();
        $table = $schema->tables[$this->resource];

        foreach($params as $key => $value) {
            try {
                if (isset($table->columns[$key])) {
                    $foreignKey = $table->columns[$key]->foreign_key;
                    if (!is_null($foreignKey) AND !is_null($value)) {
                        $this->checkForeignKey($value, $foreignKey);
                    }
                }
            }
            catch (NotFoundException $exception) {
                $this->exception->addError(new \Egg\Http\Error(array(
                    'name'          => 'not_found',
                    'description'   => $exception->getMessage(),
                )));
            }
        }
        if ($this->exception->hasErrors()) {
            throw $this->exception;
        }
    }

    protected function checkForeignKey($value, $foreignKey)
    {
        $foreignColumn = $foreignKey->foreign_column;
        $this->checkEntityExists($foreignColumn->table->name, [$foreignColumn->name => $value]);
    }

    protected function checkUniqueKeys(array $params, array $exceptParams = [])
    {
        $schema = $this->container['schema']->getData();
        $table = $schema->tables[$this->resource];

        foreach($table->unique_keys as $uniqueKey) {
            try {
                $keys = array_keys($uniqueKey->columns);
                if (count(array_diff($keys, array_keys($params))) == 0) {
                    $uniqueParams = [];
                    foreach($keys as $key) {
                        $uniqueParams[$key] = $params[$key];
                    }
                    $this->checkEntityUnique($this->resource, $uniqueParams, $exceptParams);
                }
            }
            catch (NotUniqueException $exception) {
                $this->exception->addError(new \Egg\Http\Error(array(
                    'name'          => 'not_unique',
                    'description'   => $exception->getMessage(),
                )));
            }
        }
        if ($this->exception->hasErrors()) {
            throw $this->exception;
        }
    }

    protected function checkParamNotNull($key, $value)
    {
        if (is_null($value)) {
            throw new InvalidContentException(sprintf('Param "%s" is null', $key));
        }
    }

    protected function checkParamType($key, $value, $type, array $options = [])
    {
        switch ($type) {
            case 'integer':
                $unsigned = isset($options['unsigned']) ? $options['unsigned'] : false;
                return $this->checkParamTypeInteger($key, $value, $unsigned);
            case 'string':
                $maxLength = isset($options['maxLength']) ? $options['maxLength'] : false;
                return $this->checkParamTypeString($key, $value, $maxLength);
            case 'boolean':
                return $this->checkParamTypeBool($key, $value);
            case 'float':
                $unsigned = isset($options['unsigned']) ? $options['unsigned'] : false;
                return $this->checkParamTypeFloat($key, $value, $unsigned);
            case 'date':
                return $this->checkParamTypeDate($key, $value);
        }
    }

    protected function checkParamTypeInteger($key, $value, $unsigned = false)
    {
        if (!is_numeric($value)) {
            throw new InvalidContentException(sprintf('Param "%s" integer expected', $key));
        }
        if ($unsigned) {
            $this->checkParamUnsigned($key, $value);
        }
    }

    protected function checkParamTypeString($key, $value, $maxLength = null)
    {
        if (!is_string($value)) {
            throw new InvalidContentException(sprintf('Param "%s" string expected', $key));
        }
        if (!is_null($maxLength)) {
            $this->checkParamMaxLength($key, $value, $maxLength);
        }
    }

    protected function checkParamTypeBool($key, $value)
    {
        if (!is_bool($value)) {
            throw new InvalidContentException(sprintf('Param "%s" boolean expected', $key));
        }
    }

    protected function checkParamTypeFloat($key, $value, $unsigned = false)
    {
        if (!is_numeric($value)) {
            throw new InvalidContentException(sprintf('Param "%s" float expected', $key));
        }
        if ($unsigned) {
            $this->checkParamUnsigned($key, $value);
        }
    }

    protected function checkParamTypeDate($key, $value)
    {
        if (!preg_match('/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/', $value)) {
            throw new InvalidContentException(sprintf('Param "%s" format yyyy-mm-dd expected', $key));
        }
    }

    protected function checkParamUnsigned($key, $value)
    {
        if (floatval($value) < 0) {
            throw new InvalidContentException(sprintf('Param "%s" unsigned expected', $key));
        }
    }

    protected function checkParamMaxLength($key, $value, $maxLength)
    {
        if (strlen($value) > $maxLength) {
            throw new InvalidContentException(sprintf('Param "%s" max length "%s" expected', $key, $maxLength));
        }
    }

    protected function checkEntityExists($resource, array $params)
    {
        $entity = $this->container['repository'][$resource]->selectOne($params);
        if (!$entity) {
            throw new NotFoundException(sprintf('"%s %s" not found', $resource, http_build_query($params, null, ', ')));
        }
    }

    protected function checkEntityUnique($resource, array $params, array $exceptParams)
    {
        $entity = $this->container['repository'][$resource]->selectOne($params);
        $raiseException = ($entity AND empty($exceptParams));
        foreach($exceptParams as $key => $value) {
            if (isset($entity->$key) AND $entity->$key != $value) {
                $raiseException = true;
                break;
            }
        }
        if ($raiseException) {
            throw new NotUniqueException(sprintf('"%s %s" not unique', $resource, http_build_query($params, null, ', ')));
        }
    }
}