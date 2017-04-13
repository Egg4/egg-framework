<?php

namespace Egg\Orm\Schema;

use Egg\Interfaces\SchemaInterface;

abstract class AbstractSchema implements SchemaInterface
{
    protected $settings = [];

    public function __construct(array $settings = [])
    {
        $this->settings = array_merge([
            'cache'             => null,
            'namespace'         => 'schema',
        ], $settings);
    }

    protected abstract function getName();
    protected abstract function getTables();
    protected abstract function getColumns();
    protected abstract function getForeignKeys();
    protected abstract function getUniqueKeys();

    public function getData()
    {
        $name = $this->getName();
        $key = $this->buildCacheKey($name);
        if ($this->settings['cache']) {
            $data = $this->settings['cache']->get($key);
            if ($data) return $data;
        }

        $data = $this->buildSchema($name);

        if ($this->settings['cache']) {
            $this->settings['cache']->set($key, $data);
        }

        return $data;
    }

    protected function buildCacheKey($key)
    {
        return $this->settings['namespace'] ? $this->settings['namespace'] . '.' . $key : $key;
    }

    protected function buildSchema($name)
    {
        $schema = (object) [
            'name'      => $name,
            'tables'    => [],
        ];
        $this->buildTables($schema);
        $this->buildColumns($schema);
        $this->buildForeignKeys($schema);
        $this->buildUniqueKeys($schema);

        return $schema;
    }

    protected function buildTables($schema)
    {
        $tables = $this->getTables();
        foreach ($tables as $table) {
            $table = array_merge([
                'name'          => '',
                'engine'        => '',
                'schema'        => $schema,
                'columns'       => [],
                'foreign_keys'  => [],
                'unique_keys'   => [],
            ], $table);
            $schema->tables[$table['name']] = (object) $table;
        }
    }

    protected function buildColumns($schema)
    {
        $columns = $this->getColumns();
        foreach ($columns as $column) {
            $table = $schema->tables[$column['table_name']];
            $column = array_merge([
                'name'              => '',
                'table'             => $table,
                'type'              => '',
                'primary'           => false,
                'nullable'          => false,
                'default'           => null,
                'unsigned'          => false,
                'auto_increment'    => false,
                'max_length'        => null,
                'foreign_key'       => null,
            ], $column);
            unset($column['table_name']);
            $table->columns[$column['name']] = (object) $column;
        }
    }

    protected function buildForeignKeys($schema)
    {
        $foreignKeys = $this->getForeignKeys();
        foreach ($foreignKeys as $foreignKey) {
            $column = $schema->tables[$foreignKey['table_name']]->columns[$foreignKey['column_name']];
            $foreignColumn = $schema->tables[$foreignKey['foreign_table_name']]->columns[$foreignKey['foreign_column_name']];
            $foreignKey = array_merge([
                'name'              => '',
                'column'            => $column,
                'foreign_column'    => $foreignColumn,
            ], $foreignKey);
            unset($foreignKey['table_name']);
            unset($foreignKey['column_name']);
            unset($foreignKey['foreign_table_name']);
            unset($foreignKey['foreign_column_name']);
            $column->foreign_key = (object) $foreignKey;
            $column->table->foreign_keys[$foreignKey['name']] = (object) $foreignKey;
        }
    }

    protected function buildUniqueKeys($schema)
    {
        $uniqueKeys = $this->getUniqueKeys();
        foreach ($uniqueKeys as $uniqueKey) {
            $table = $schema->tables[$uniqueKey['table_name']];
            if (!isset($table->unique_keys[$uniqueKey['name']])) {
                $uniqueKey = array_merge([
                    'name'          => '',
                    'columns'       => [$uniqueKey['column_name'] => $table->columns[$uniqueKey['column_name']]],
                ], $uniqueKey);
                unset($uniqueKey['table_name']);
                unset($uniqueKey['column_name']);
                $table->unique_keys[$uniqueKey['name']] = (object) $uniqueKey;
            }
            else {
                $table->unique_keys[$uniqueKey['name']]->columns[$uniqueKey['column_name']] = $table->columns[$uniqueKey['column_name']];
            }
        }
    }
}