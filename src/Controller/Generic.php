<?php

namespace Egg\Controller;

class Generic extends AbstractController
{
    protected $repository;

    public function init()
    {
        $resource = $this->container['request']->getAttribute('resource');
        $this->repository = $this->container['repository'][$resource];
    }

    public function select(array $filterParams, array $sortParams, array $rangeParams)
    {
        return $this->repository->selectAll($filterParams, $sortParams, $rangeParams);
    }

    public function search(array $filterParams, array $sortParams, array $rangeParams)
    {
        return $this->repository->selectAll($filterParams, $sortParams, $rangeParams);
    }

    public function read($id)
    {
        $entity = $this->repository->selectOneById($id);

        return $entity;
    }

    public function create(array $params)
    {
        $id = $this->repository->insert($params);
        $entity = $this->repository->selectOneById($id);

        return $entity;
    }

    public function replace($id, array $params)
    {
        return $this->update($id, $params);
    }

    public function update($id, array $params)
    {
        $this->repository->updateById($params, $id);
        $entity = $this->repository->selectOneById($id);

        return $entity;
    }

    public function delete($id)
    {
        $this->repository->deleteById($id);
    }
}