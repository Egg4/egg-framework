<?php

namespace Egg\Orm\Schema;

class Mysql extends AbstractSchema
{
    protected $database;
    protected $name;

    public function __construct(array $settings = [])
    {
        parent::__construct(array_merge([
            'database'          => null,
            'entitySet.class'   => \Egg\Orm\EntitySet\Generic::class,
            'entity.class'      => \Egg\Orm\Entity\Generic::class,
        ], $settings));

        if (is_null($this->settings['database'])) {
            throw new \Exception('Database not set');
        }

        $this->database = $this->settings['database'];
        $this->name = $this->database->getName();
    }

    protected function getName()
    {
        return $this->name;
    }

    protected function getTables()
    {
        $sql = sprintf("SELECT *
                        FROM `information_schema`.`tables`
                        WHERE `table_schema` = '%s'
                        ORDER BY `table_name`
                       ", $this->name);

        $statement = $this->database->execute($sql);
        $entities = $statement->fetchEntitySet($this->settings['entitySet.class'], $this->settings['entity.class']);
        $rows = $entities->toArray();

        $tables = [];
        foreach ($rows as $row) {
            $tables[] = [
                'name'      => $row['TABLE_NAME'],
                'engine'    => $row['ENGINE'],
            ];
        }

        return $tables;
    }

    protected function getColumns()
    {
        $sql = sprintf("SELECT *
                        FROM `information_schema`.`columns`
                        WHERE `table_schema` = '%s'
                        ORDER BY `ordinal_position`
                       ", $this->name);

        $statement = $this->database->execute($sql);
        $entities = $statement->fetchEntitySet($this->settings['entitySet.class'], $this->settings['entity.class']);
        $rows = $entities->toArray();

        $columns = [];
        foreach ($rows as $row) {
            $columns[] = [
                'name'              => $row['COLUMN_NAME'],
                'table_name'        => $row['TABLE_NAME'],
                'type'              => $this->normalizeColumnType($row['DATA_TYPE']),
                'primary'           => ($row['COLUMN_KEY'] == 'PRI'),
                'nullable'          => ($row['IS_NULLABLE'] != 'NO'),
                'default'           => $row['COLUMN_DEFAULT'],
                'unsigned'          => (strpos($row['COLUMN_TYPE'], 'unsigned') !== false),
                'auto_increment'    => (strpos($row['EXTRA'], 'auto_increment') !== false),
                'max_length'        => $row['CHARACTER_MAXIMUM_LENGTH'],
            ];
        }

        return $columns;
    }

    protected function getForeignKeys()
    {
        $sql = sprintf("SELECT kcu.*
                        FROM `information_schema`.`table_constraints` as tc, `information_schema`.`key_column_usage` as kcu
                        WHERE tc.`table_schema` = kcu.`table_schema`
                        AND tc.`table_name` = kcu.`table_name`
                        AND tc.`constraint_name` = kcu.`constraint_name`
                        AND tc.`table_schema` = '%s'
                        AND tc.`constraint_type` = 'FOREIGN KEY'
                        ORDER BY kcu.`constraint_name`
                       ", $this->name);
        $statement = $this->database->execute($sql);
        $entities = $statement->fetchEntitySet($this->settings['entitySet.class'], $this->settings['entity.class']);
        $rows = $entities->toArray();

        $foreignKeys = [];
        foreach ($rows as $row) {
            $foreignKeys[] = [
                'name'                  => $row['CONSTRAINT_NAME'],
                'table_name'            => $row['TABLE_NAME'],
                'column_name'           => $row['COLUMN_NAME'],
                'foreign_table_name'    => $row['REFERENCED_TABLE_NAME'],
                'foreign_column_name'   => $row['REFERENCED_COLUMN_NAME'],
            ];
        }

        return $foreignKeys;
    }

    protected function getUniqueKeys()
    {
        $sql = sprintf("SELECT kcu.*
                        FROM `information_schema`.`table_constraints` as tc, `information_schema`.`key_column_usage` as kcu
                        WHERE tc.`table_schema` = kcu.`table_schema`
                        AND tc.`table_name` = kcu.`table_name`
                        AND tc.`constraint_name` = kcu.`constraint_name`
                        AND tc.`table_schema` = '%s'
                        AND tc.`constraint_type` = 'UNIQUE'
                        ORDER BY kcu.`constraint_name`, kcu.`table_name`, kcu.`ordinal_position`
                       ", $this->name);
        $statement = $this->database->execute($sql);
        $entities = $statement->fetchEntitySet($this->settings['entitySet.class'], $this->settings['entity.class']);
        $rows = $entities->toArray();

        $uniqueKeys = [];
        foreach ($rows as $row) {
            $uniqueKeys[] = [
                'name'                  => $row['CONSTRAINT_NAME'],
                'table_name'            => $row['TABLE_NAME'],
                'column_name'           => $row['COLUMN_NAME'],
            ];
        }

        return $uniqueKeys;
    }

    protected function normalizeColumnType($type)
    {
        switch ($type) {
            case 'int':
            case 'bigint':
            case 'smallint':
            case 'mediumint':
                return 'integer';
            case 'char':
            case 'varchar':
            case 'text':
                return 'string';
            case 'tinyint':
                return 'boolean';
            case 'decimal':
            case 'double':
            case 'float':
                return 'float';
            case 'date':
                return 'date';
            default:
                return $type;
        }
    }
}