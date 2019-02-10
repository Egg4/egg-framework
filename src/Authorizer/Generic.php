<?php

namespace Egg\Authorizer;

class Generic extends AbstractAuthorizer
{
    protected $container;
    protected $resource;

    public function __construct(array $settings = [])
    {
        parent::__construct(array_merge([
            'container'     => null,
            'resource'      => null,
        ], $settings));

        $this->container = $this->settings['container'];
        $this->resource = $this->settings['resource'];
    }

    protected function analyse($action)
    {
        return [];
    }

    public function select(array $filterParams, array $sortParams, array $rangeParams)
    {
        return array_merge($filterParams, $this->analyse('select'));
    }

    public function search(array $filterParams, array $sortParams, array $rangeParams)
    {
        return array_merge($filterParams, $this->analyse('search'));
    }

    public function read($id)
    {
        $filterParams = $this->analyse('read');
        if (!empty($filterParams)) {
            $this->checkEntityExists(array_merge(['id' => $id], $filterParams));
        }
    }

    public function create(array $params)
    {
        $filterParams = $this->analyse('create');
        if (!empty($filterParams)) {
            $this->checkParams($params, $filterParams);
        }
    }

    public function replace($id, array $params)
    {
        $filterParams = $this->analyse('replace');
        if (!empty($filterParams)) {
            $this->checkParams($params, $filterParams);
            $this->checkEntityExists(array_merge(['id' => $id], $filterParams));
        }
    }

    public function update($id, array $params)
    {
        $filterParams = $this->analyse('update');
        if (!empty($filterParams)) {
            $this->checkParams($params, $filterParams);
            $this->checkEntityExists(array_merge(['id' => $id], $filterParams));
        }
    }

    public function delete($id)
    {
        $filterParams = $this->analyse('delete');
        if (!empty($filterParams)) {
            $this->checkEntityExists(array_merge(['id' => $id], $filterParams));
        }
    }

    public function __call($action, $arguments)
    {
        $params = isset($arguments[0]) ? $arguments[0] : [];
        $filterParams = $this->analyse($action);
        if (!empty($filterParams)) {
            $this->checkParams($params, $filterParams);
            $this->checkEntityExists(array_merge($params, $filterParams));
        }
    }

    protected function checkParams($params, $filterParams)
    {
        foreach($filterParams as $key => $value) {
            if (!in_array($key, array_keys($params))) {
                continue;
            }
            if (is_array($value)
                AND in_array($params[$key], $value)
                OR $value == $params[$key]
            ) {
                continue;
            }
            throw new \Egg\Http\Exception($this->container['response'], 403, new \Egg\Http\Error(array(
                'name'          => 'not_allowed',
                'description'   => 'Access denied',
            )));
        }
    }

    protected function checkEntityExists($params)
    {
        $repository = $this->container['repository'][$this->resource];
        $entity = $repository->selectOne($params);
        if (!$entity) {
            throw new \Egg\Http\Exception($this->container['response'], 403, new \Egg\Http\Error(array(
                'name'          => 'not_allowed',
                'description'   => 'Access denied',
            )));
        }
    }
}