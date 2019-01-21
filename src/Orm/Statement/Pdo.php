<?php

namespace Egg\Orm\Statement;

class Pdo extends AbstractStatement
{
    protected $pdoStatement;

    public function __construct(\PDOStatement $pdoStatement)
    {
        $this->pdoStatement = $pdoStatement;
        $this->meta = $this->getMeta();
    }

    protected function getMeta()
    {
        $meta = [];
        $columnCount = $this->pdoStatement->columnCount();
        if ($columnCount > 0) {
            foreach(range(0, $columnCount - 1) as $i) {
                $meta[$i] = $this->pdoStatement->getColumnMeta($i);
            }
        }

        return $meta;
    }

    public function entityCount()
    {
        return $this->pdoStatement->rowCount();
    }

    public function fetchEntitySet($entitySetClass, $entityClass)
    {
        $entityBase = new $entityClass;

        $results = [];
        while($row = $this->fetchRow()) {
            $entity = clone $entityBase;
            $entity->hydrate($row);
            $results[] = $entity;
        }

        return new $entitySetClass($results);
    }

    public function fetchEntity($entityClass)
    {
        $row = $this->fetchRow();
        if (!$row) return null;

        $entity = new $entityClass;
        $entity->hydrate($row);

        return $entity;
    }

    protected function fetchRow()
    {
        $row = $this->pdoStatement->fetch(\PDO::FETCH_NUM);
        if (!$row) return null;

        $data = [];
        foreach($row as $i => $value) {
            $key = $this->meta[$i]['name'];
            $type = $this->meta[$i]['native_type'];
            $data[$key] = $this->castValue($value, $type);
        }

        return $data;
    }

    protected function castValue($value, $type)
    {
        if (is_null($value)) {
            return $value;
        }
        switch ($type) {
            case 'LONGLONG':
            case 'LONG':
            case 'SHORT':
                return intval($value);
            case 'TINY':
                return boolval($value);
            case 'STRING':
            case 'VAR_STRING':
                return strval($value);
            case 'NEWDECIMAL':
            case 'DOUBLE':
            case 'FLOAT':
                return floatval($value);
            default:
                return $value;
        }
    }
}