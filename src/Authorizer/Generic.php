<?php

namespace Egg\Authorizer;

class Generic extends AbstractAuthorizer
{
    protected $settings;
    protected $repository;

    public function __construct(array $settings = [])
    {
        $this->settings = array_merge([
            'actions' => [],
            'schema.self.resource' => '',
            'schema.self.attribute' => '',
            'schema.reference.resource' => '',
            'schema.reference.attribute' => '',
        ], $settings);
        $this->container = $settings['container'];
        $this->resource = $settings['resource'];
    }

    protected function canAccess($authentication, $right)
    {
        list($field, $role) = explode('=', $right);
        list($resource, $attribute) = explode('.', $field);

        if ($authentication['resource'] != $resource) {
            return false;
        }
        if ($role == '*') {
            return true;
        }

        return in_array($authentication[$attribute], explode(',', $role));
    }

    public function getAuthFilterParams()
    {
        $selfAttribute = $this->settings['schema.self.attribute'];
        $referenceResource = $this->settings['schema.reference.resource'];
        $referenceAttribute = $this->settings['schema.reference.attribute'];
        $authentication = $this->container['request']->getAttribute('authentication');
        if ($authentication['resource'] == $referenceResource) {
            return [$selfAttribute => $authentication[$referenceAttribute]];
        }
        $referenceAuthorizer = $this->container['authorizer'][$referenceResource];
        $params = $referenceAuthorizer->getAuthFilterParams();
        $referenceRepository = $this->container['repository'][$referenceResource];
        $entities = $referenceRepository->selectAll($params);
        $ids = array_map(function($entity) {
            return $entity['id'];
        }, $entities);

        return [$selfAttribute => $ids];
    }

    protected function analyse($action)
    {
        $right = 'deny';
        if (isset($this->settings['actions']['*'])) {
            $right = $this->settings['actions']['*'];
        }
        if (isset($this->settings['actions'][$action])) {
            $right = $this->settings['actions'][$action];
        }
        if ($right == 'deny') {
            throw new \Egg\Http\Exception($this->container['response'], 403, new \Egg\Http\Error(array(
                'name'          => 'not_allowed',
                'description'   => 'Access denied',
            )));
        }
        if ($right == 'allow') {
            return [];
        }

        $authentication = $this->container['request']->getAttribute('authentication');
        if (!$authentication) {
            throw new \Egg\Http\Exception($this->container['response'], 403, new \Egg\Http\Error(array(
                'name'          => 'authentication_required',
                'description'   => 'Authentication is required',
            )));
        }

        if (!$this->canAccess($authentication, $right)) {
            throw new \Egg\Http\Exception($this->container['response'], 403, new \Egg\Http\Error(array(
                'name'          => 'not_allowed',
                'description'   => 'Access denied',
            )));
        }

        return $this->getAuthFilterParams();
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
        $this->analyse($action);
    }

    protected function checkParams($params, $filterParams)
    {
        foreach($filterParams as $key => $value) {
            if (!isset($params[$key])) {
                continue;
            }
            if (is_array($filterParams[$key])
                AND in_array($params[$key], $filterParams[$key])
                OR $filterParams[$key] == $params[$key]
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