<?php

namespace Egg\Authorizer;

class Generic extends AbstractAuthorizer
{
    protected $repository;

    public function init()
    {
        $resource = $this->container['request']->getAttribute('resource');
        $this->repository = $this->container['repository'][$resource];
    }

    public function select(array $filterParams, array $sortParams, array $rangeParams)
    {

    }

    public function search(array $filterParams, array $sortParams, array $rangeParams)
    {

    }

    public function read($id)
    {

    }

    public function create(array $params)
    {

    }

    public function replace($id, array $params)
    {

    }

    public function update($id, array $params)
    {

    }

    public function delete($id)
    {

    }
}