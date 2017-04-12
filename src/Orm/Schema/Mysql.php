<?php

namespace Egg\Orm\Schema;

class Mysql extends AbstractSchema
{
    public function __construct(array $settings = [])
    {
        $this->settings = array_merge([
            'database'          => null,
            'cache'             => null,
            'namespace'         => 'schema',
            'entitySet.class'   => \Egg\Orm\EntitySet\Generic::class,
            'entity.class'      => \Egg\Orm\Entity\Generic::class,
        ], $settings);
    }

    public function getData()
    {
        $key = $this->buildKey($this->settings['database']->getName());
        if ($this->settings['cache']) {
            $data = $this->settings['cache']->get($key);
            if ($data) return $data;
        }

        $data = $this->buildSchema();

        if ($this->settings['cache']) {
            $this->settings['cache']->set($key, $data);
        }

        return $data;
    }

    protected function buildKey($key)
    {
        return $this->settings['namespace'] ? $this->settings['namespace'] . '.' . $key : $key;
    }

    protected function buildSchema()
    {
        $database = new \stdClass();
        $database->name = $this->settings['database']->getName();
        $database->tables = [];
        $this->buildTables($database);
        $this->buildColumns($database);
        $this->buildForeignKeys($database);
        $this->buildUniqueKeys($database);

        return $database;
    }

    protected function buildTables($database)
    {
        $tables = $this->getTables($database->name);
        foreach ($tables as $table) {
            $table->database = $database;
            $table->columns = [];
            $table->foreign_keys = [];
            $table->unique_keys = [];
            $database->tables[$table->name] = $table;
        }
    }

    protected function buildColumns($database)
    {
        $columns = $this->getColumns($database->name);
        foreach ($columns as $column) {
            $column->table = $database->tables[$column->table_name];
            $column->foreign_key = null;
            unset($column->table_name);
            $column->table->columns[$column->name] = $column;
        }
    }

    protected function buildForeignKeys($database)
    {
        $foreignKeys = $this->getForeignKeys($database->name);
        foreach ($foreignKeys as $foreignKey) {
            $foreignKey->column = $database->tables[$foreignKey->table_name]->columns[$foreignKey->column_name];
            $foreignKey->foreign_column = $database->tables[$foreignKey->reference_table_name]->columns[$foreignKey->reference_column_name];
            unset($foreignKey->table_name);
            unset($foreignKey->column_name);
            unset($foreignKey->reference_table_name);
            unset($foreignKey->reference_column_name);
            $foreignKey->column->foreign_key = $foreignKey;
            $foreignKey->column->table->foreign_keys[$foreignKey->name] = $foreignKey;
        }
    }

    protected function buildUniqueKeys($database)
    {
        $uniqueKeys = $this->getUniqueKeys($database->name);
        foreach ($uniqueKeys as $uniqueKey) {
            $table = $database->tables[$uniqueKey->table_name];
            if (!isset($table->unique_keys[$uniqueKey->name])) {
                $uniqueKey->columns[$uniqueKey->column_name] = $table->columns[$uniqueKey->column_name];
                unset($uniqueKey->table_name);
                unset($uniqueKey->column_name);
                $table->unique_keys[$uniqueKey->name] = $uniqueKey;
            }
            else {
                $table->unique_keys[$uniqueKey->name]->columns[$uniqueKey->column_name] = $table->columns[$uniqueKey->column_name];
            }
        }
    }

    protected function getTables($databaseName)
    {
        $sql = sprintf("SELECT *
                        FROM `information_schema`.`tables`
                        WHERE `table_schema` = '%s'
                        ORDER BY `table_name`
                       ", $databaseName);

        $statement = $this->settings['database']->execute($sql);
        $entities = $statement->fetchEntitySet($this->settings['entitySet.class'], $this->settings['entity.class']);
        $rows = $entities->toArray();

        $tables = [];
        foreach ($rows as $row) {
            $tables[] = (object) [
                'name'      => $row['TABLE_NAME'],
                'engine'    => $row['ENGINE'],
            ];
        }

        return $tables;
    }

    protected function getColumns($databaseName)
    {
        $sql = sprintf("SELECT *
                        FROM `information_schema`.`columns`
                        WHERE `table_schema` = '%s'
                        ORDER BY `ordinal_position`
                       ", $databaseName);

        $statement = $this->settings['database']->execute($sql);
        $entities = $statement->fetchEntitySet($this->settings['entitySet.class'], $this->settings['entity.class']);
        $rows = $entities->toArray();

        $columns = [];
        foreach ($rows as $row) {
            $columns[] = (object) [
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

    protected function getForeignKeys($databaseName)
    {
        $sql = sprintf("SELECT kcu.*
                        FROM `information_schema`.`table_constraints` as tc, `information_schema`.`key_column_usage` as kcu
                        WHERE tc.`table_schema` = kcu.`table_schema`
                        AND tc.`table_name` = kcu.`table_name`
                        AND tc.`constraint_name` = kcu.`constraint_name`
                        AND tc.`table_schema` = '%s'
                        AND tc.`constraint_type` = 'FOREIGN KEY'
                        ORDER BY kcu.`constraint_name`
                       ", $databaseName);
        $statement = $this->settings['database']->execute($sql);
        $entities = $statement->fetchEntitySet($this->settings['entitySet.class'], $this->settings['entity.class']);
        $rows = $entities->toArray();

        $foreignKeys = [];
        foreach ($rows as $row) {
            $foreignKeys[] = (object) [
                'name'                  => $row['CONSTRAINT_NAME'],
                'table_name'            => $row['TABLE_NAME'],
                'column_name'           => $row['COLUMN_NAME'],
                'reference_table_name'  => $row['REFERENCED_TABLE_NAME'],
                'reference_column_name' => $row['REFERENCED_COLUMN_NAME'],
            ];
        }

        return $foreignKeys;
    }

    protected function getUniqueKeys($databaseName)
    {
        $sql = sprintf("SELECT kcu.*
                        FROM `information_schema`.`table_constraints` as tc, `information_schema`.`key_column_usage` as kcu
                        WHERE tc.`table_schema` = kcu.`table_schema`
                        AND tc.`table_name` = kcu.`table_name`
                        AND tc.`constraint_name` = kcu.`constraint_name`
                        AND tc.`table_schema` = '%s'
                        AND tc.`constraint_type` = 'UNIQUE'
                        ORDER BY kcu.`constraint_name`, kcu.`table_name`, kcu.`ordinal_position`
                       ", $databaseName);
        $statement = $this->settings['database']->execute($sql);
        $entities = $statement->fetchEntitySet($this->settings['entitySet.class'], $this->settings['entity.class']);
        $rows = $entities->toArray();

        $uniqueKeys = [];
        foreach ($rows as $row) {
            $uniqueKeys[] = (object) [
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
            case 'int':     return 'integer';
            case 'char':    return 'string';
            case 'varchar': return 'string';
            case 'text':    return 'string';
            case 'tinyint': return 'boolean';
            case 'float':   return 'float';
            default:        return $type;
        }
    }
}