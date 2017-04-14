<?php

namespace Egg\Orm\Repository;

class Generic extends AbstractRepository
{
    protected $container;
    protected $database;
    protected $resource;

    public function __construct(array $settings = [])
    {
        parent::__construct(array_merge([
            'container'         => null,
            'resource'          => null,
            'entitySet.class'   => \Egg\Orm\EntitySet\Generic::class,
            'entity.class'      => \Egg\Orm\Entity\Generic::class,
        ], $settings));

        $this->container = $this->settings['container'];
        $this->database = $this->container['database'];
        $this->resource = $this->settings['resource'];
    }

    public function insert(array $data)
    {
        $sql    = $this->database->prepareInsert($this->resource, $data);
        $params = $this->database->prepareParams($data);
        $this->database->execute($sql, $params);

        return $this->database->lastInsertId();
    }

    public function delete(array $where = [])
    {
        $sql    = $this->database->prepareDelete($this->resource, $where);
        $params = $this->database->prepareParams($where);
        $statement = $this->database->execute($sql, $params);

        return $statement->entityCount();
    }

    public function update(array $data, array $where = [])
    {
        $sql    = $this->database->prepareUpdate($this->resource, $data, $where);
        $params = $this->database->prepareParams($data, $where);
        $statement = $this->database->execute($sql, $params);

        return $statement->entityCount();
    }

    public function selectAll(array $where = [], array $orderBy = [], array $limit = [])
    {
        $sql    = $this->database->prepareSelect($this->resource, $where, $orderBy, $limit);
        $params = $this->database->prepareParams($where);
        $statement = $this->database->execute($sql, $params);

        return $statement->fetchEntitySet($this->settings['entitySet.class'], $this->settings['entity.class']);
    }

    public function selectOne(array $where = [])
    {
        $sql    = $this->database->prepareSelect($this->resource, $where, [], ['limit' => 1]);
        $params = $this->database->prepareParams($where);
        $statement = $this->database->execute($sql, $params);

        return $statement->fetchEntity($this->settings['entity.class']);
    }
}