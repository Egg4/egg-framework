<?php

namespace Egg\Orm\Schema;

class Mysql extends AbstractSchema
{
    protected $data = [];

    public function __construct(array $settings = [])
    {
        $this->settings = array_merge([
            'database'           => null,
            'cache'              => null,
            'entitySet.class'    => \Egg\Orm\EntitySet\Generic::class,
            'entity.class'       => \Egg\Orm\Entity\Generic::class,
        ], $settings);
    }

    public function getData()
    {
        /*
        $key = 'schema.' . $this->database;
        if ($this->cache) {
            $data = $this->cache->get($key);
            if ($data) return $data;
        }

        if ($this->cache) {
            $this->cache->set($key, $database);
        }*/

        return $this->getSchema();
    }

    protected function getSchema()
    {
        $databaseName = $this->settings['database']->getName();

        return [
            'name'          => $databaseName,
            'tables'        => $this->getTables($databaseName),
            //'foreign.keys'  => $this->getForeignKeys($databaseName),
        ];
    }

    protected function getTables($databaseName)
    {
        $tables = [];

        $sql = sprintf("SELECT *
                        FROM `information_schema`.`tables`
                        WHERE `table_schema` = '%s'
                       ", $databaseName);

        $statement = $this->settings['database']->execute($sql);
        $entities = $statement->fetchEntitySet($this->settings['entitySet.class'], $this->settings['entity.class']);
        $rows = $entities->toArray();

        foreach ($rows as $row) {
            $tables[$row['TABLE_NAME']] = [
                'name'          => $row['TABLE_NAME'],
                'engine'        => $row['ENGINE'],
                'columns'       => $this->getColumns($databaseName, $row['TABLE_NAME']),
                //'unique.keys'   => '',
            ];
        }

        return $tables;
    }

    protected function getColumns($databaseName, $tableName)
    {
        $columns = [];

        $sql = sprintf("SELECT *
                        FROM `information_schema`.`columns`
                        WHERE `table_schema` = '%s'
                        AND `table_name` = '%s'
                        ORDER BY `ordinal_position`
                       ", $databaseName, $tableName);

        $statement = $this->settings['database']->execute($sql);
        $entities = $statement->fetchEntitySet($this->settings['entitySet.class'], $this->settings['entity.class']);
        $rows = $entities->toArray();

        foreach ($rows as $row) {
            $columns[$row['COLUMN_NAME']] = [
                'name'              => $row['COLUMN_NAME'],
                'type'              => $this->normalizeColumnType($row['DATA_TYPE']),
                'primary'           => ($row['COLUMN_KEY'] == 'PRI'),
                'nullable'          => ($row['IS_NULLABLE'] != 'NO'),
                'default'           => $row['COLUMN_DEFAULT'],
                'unsigned'          => (strpos($row['COLUMN_TYPE'], 'unsigned') !== false),
                'auto.increment'    => (strpos($row['EXTRA'], 'auto_increment') !== false),
                'max.length'        => $row['CHARACTER_MAXIMUM_LENGTH'],
            ];
        }

        return $columns;
    }

    /*
    protected function getForeignKeys($database)
    {
        $foreignKeys = array();

        $sql = sprintf("SELECT kcu.*
                        FROM `information_schema`.`table_constraints` as tc, `information_schema`.`key_column_usage` as kcu
                        WHERE tc.`table_schema` = kcu.`table_schema`
                        AND tc.`table_name` = kcu.`table_name`
                        AND tc.`constraint_name` = kcu.`constraint_name`
                        AND tc.`table_schema` = '%s'
                        AND tc.`constraint_type` = 'FOREIGN KEY'
                       ", $database->name);
        $rows = $this->pdo->query($sql)->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($rows as $row) {
            $foreignKey = new \Egg\Orm\Schema\ForeignKey();
            $foreignKey->name = $row['CONSTRAINT_NAME'];
            $foreignKey->database = $database;
            $foreignKey->column = $database->tables[$row['TABLE_NAME']]->columns[$row['COLUMN_NAME']];
            $foreignKey->referencedColumn = $database->tables[$row['REFERENCED_TABLE_NAME']]->columns[$row['REFERENCED_COLUMN_NAME']];

            $foreignKeys[$foreignKey->name] = $foreignKey;
        }

        return $foreignKeys;
    }

    protected function getUniqueKeys($table)
    {
        $uniqueKeys = array();

        $sql = sprintf("SELECT kcu.*
                        FROM `information_schema`.`table_constraints` as tc, `information_schema`.`key_column_usage` as kcu
                        WHERE tc.`table_schema` = kcu.`table_schema`
                        AND tc.`table_name` = kcu.`table_name`
                        AND tc.`constraint_name` = kcu.`constraint_name`
                        AND tc.`table_schema` = '%s'
                        AND tc.`table_name` = '%s'
                        AND tc.`constraint_type` = 'UNIQUE'
                        ORDER BY kcu.`ordinal_position`
                       ", $table->database->name, $table->name);
        $rows = $this->pdo->query($sql)->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($rows as $row) {
            $name = $row['CONSTRAINT_NAME'];
            if (!isset($uniqueKeys[$name])) {
                $uniqueKey = new \Egg\Orm\Schema\UniqueKey();
                $uniqueKey->name = $name;
                $uniqueKey->table = $table;
                $uniqueKey->columns[] = $table->columns[$row['COLUMN_NAME']];

                $uniqueKeys[$uniqueKey->name] = $uniqueKey;
            }
            else {
                $uniqueKey = $uniqueKeys[$name];
                $uniqueKey->columns[] = $table->columns[$row['COLUMN_NAME']];
            }
        }

        return $uniqueKeys;
    }
    */

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