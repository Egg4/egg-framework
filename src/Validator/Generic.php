<?php

namespace Egg\Validator;

class Generic extends AbstractValidator
{
    protected $resource;

    public function __construct(array $settings = [])
    {
        $this->settings = array_merge([

        ], $settings);
        $this->container = $settings['container'];
        $this->resource = $settings['resource'];
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
        $this->checkContentNotEmpty($params);
    }

    public function replace($id, array $params)
    {
        $this->checkEntityExists($id);
        $this->checkContentNotEmpty($params);
    }

    public function update($id, array $params)
    {
        $this->checkEntityExists($id);
        $this->checkContentNotEmpty($params);
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
                'description'   => sprintf('Id "%s" not found', $id),
            )));
        }
    }

    protected function checkContentNotEmpty($content)
    {
        if (empty($content)) {
            $this->exception->addError(new \Egg\Http\Error(array(
                'name'          => 'invalid_content',
                'description'   => 'No content',
            )));
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

    protected function requireParams(array $keys, array $params)
    {
        foreach($keys as $key) {
            $this->requireParam($key, $params);
        }
    }
}