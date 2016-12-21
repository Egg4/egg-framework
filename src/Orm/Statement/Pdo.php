<?php

namespace Egg\Orm\Statement;

class Pdo extends AbstractStatement
{
    protected $pdoStatement;

    public function __construct(\PDOStatement $pdoStatement)
    {
        $this->pdoStatement = $pdoStatement;
    }

    public function entityCount()
    {
        return $this->pdoStatement->rowCount();
    }

    public function fetchEntitySet($entitySetClass, $entityClass)
    {
        $results = $this->pdoStatement->fetchAll(\PDO::FETCH_CLASS, $entityClass);

        return new $entitySetClass($results);
    }

    public function fetchEntity($entityClass)
    {
        return $this->pdoStatement->fetchObject($entityClass);
    }
}