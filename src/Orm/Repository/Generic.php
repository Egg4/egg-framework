<?php

namespace Egg\Orm\Repository;

class Generic extends AbstractRepository
{
    public function __construct(array $settings = [])
    {
        $this->settings = array_merge([
            'database'          => null,
            'table'             => null,
            'entitySet.class'    => \Egg\Orm\EntitySet\Generic::class,
            'entity.class'       => \Egg\Orm\Entity\Generic::class,
        ], $settings);
    }

    public function getDatabase()
    {
        return $this->settings['database'];
    }

    public function insert(array $data)
    {
        $sql    = $this->settings['database']->prepareInsert($this->settings['table'], $data);
        $params = $this->settings['database']->prepareParams($data);
        $this->settings['database']->execute($sql, $params);

        return $this->settings['database']->lastInsertId();
    }

    public function delete(array $where = [])
    {
        $sql    = $this->settings['database']->prepareDelete($this->settings['table'], $where);
        $params = $this->settings['database']->prepareParams($where);
        $statement = $this->settings['database']->execute($sql, $params);

        return $statement->entityCount();
    }

    public function update(array $data, array $where = [])
    {
        $sql    = $this->settings['database']->prepareUpdate($this->settings['table'], $data, $where);
        $params = $this->settings['database']->prepareParams($data, $where);
        $statement = $this->settings['database']->execute($sql, $params);

        return $statement->entityCount();
    }

    public function selectAll(array $where = [], array $orderBy = [], array $limit = [])
    {
        $sql    = $this->settings['database']->prepareSelect($this->settings['table'], $where, $orderBy, $limit);
        $params = $this->settings['database']->prepareParams($where);
        $statement = $this->settings['database']->execute($sql, $params);

        return $statement->fetchEntitySet($this->settings['entitySet.class'], $this->settings['entity.class']);
    }

    public function selectOne(array $where = [])
    {
        $sql    = $this->settings['database']->prepareSelect($this->settings['table'], $where, [], ['limit' => 1]);
        $params = $this->settings['database']->prepareParams($where);
        $statement = $this->settings['database']->execute($sql, $params);

        return $statement->fetchEntity($this->settings['entity.class']);
    }
}