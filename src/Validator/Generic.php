<?php

namespace Egg\Validator;

class Generic extends AbstractValidator
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

    protected function checkEntityExists($id)
    {
        $entity = $this->repository->selectOneById($id);
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
}