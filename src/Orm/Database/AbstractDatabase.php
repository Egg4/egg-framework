<?php

namespace Egg\Orm\Database;

use Egg\Interfaces\DatabaseInterface;

abstract class AbstractDatabase implements DatabaseInterface
{
    protected $settings;

    public function __construct(array $settings = [])
    {
        $this->settings = array_merge([
            'escapeIdentifier'  => function($identifier) {
                return sprintf('`%s`', $identifier);
            },
            'paramPlaceholder'  => '?',
            'wildcard'          => '%WILDCARD%',
            'floatCasting'      => 'DECIMAL(10,5)',
        ], $settings);
    }

    public function escapeIdentifier($identifier)
    {
        return $this->settings['escapeIdentifier']($identifier);
    }

    public function prepareInsert($table, array $data)
    {
        $fields = array();
        $values = array();
        foreach ($data as $key => $value) {
            $fields[] = $this->escapeIdentifier($key);
            $values[] = $this->settings['paramPlaceholder'];
        }

        return sprintf('INSERT INTO %s (%s) VALUES (%s);',
            $this->escapeIdentifier($table),
            implode(', ', $fields),
            implode(', ', $values)
        );
    }

    public function prepareDelete($table, array $where = [])
    {
        return sprintf('DELETE FROM %s%s;',
            $this->escapeIdentifier($table),
            $this->prepareWhere($where)
        );
    }

    public function prepareUpdate($table, array $data, array $where = [])
    {
        return sprintf('UPDATE %s%s%s;',
            $this->escapeIdentifier($table),
            $this->prepareSet($data),
            $this->prepareWhere($where)
        );
    }

    public function prepareSelect($table, array $where = [], array $orderBy = [], array $limit = [])
    {
        return sprintf('SELECT * FROM %s%s%s%s;',
            $this->escapeIdentifier($table),
            $this->prepareWhere($where),
            $this->prepareOrderBy($orderBy),
            $this->prepareLimit($limit)
        );
    }

    public function prepareParams()
    {
        $params = [];
        $args = func_get_args();
        foreach ($args as $i => $array) {
            if (!is_array($array)) throw new \Exception(sprintf('Arg %d array expected', $i));
            $array = array_values($array);
            foreach ($array as $value) {
                if (is_array($value)) {
                    $params = array_merge($params, array_values($value));
                }
                elseif (strpos($value, $this->settings['wildcard']) !== false) {
                    $params[] = str_replace($this->settings['wildcard'], '%', $value);
                }
                else {
                    $params[] = $value;
                }
            }
        }

        return $params;
    }

    protected function prepareSet(array $params)
    {
        $items = [];
        foreach ($params as $key => $value) {
            $items[] = sprintf('%s = %s',
                $this->escapeIdentifier($key),
                $this->settings['paramPlaceholder']
            );
        }

        return ' SET ' . implode(', ', $items);
    }

    protected function prepareWhere(array $params)
    {
        if (count($params) == 0) return '';

        $items = [];
        foreach ($params as $key => $value) {
            if (is_null($value)) {
                $items[] = sprintf('%s IS NULL',
                    $this->escapeIdentifier($key)
                );
            }
            elseif (is_array($value)) {
                $items[] = sprintf('%s IN (%s)',
                    $this->escapeIdentifier($key),
                    implode(', ', array_fill(0, count($value), $this->settings['paramPlaceholder']))
                );
            }
            elseif (is_float($value)) {
                $items[] = sprintf('CAST(%s AS %s) = CAST(%s AS %s)',
                    $this->escapeIdentifier($key),
                    $this->settings['floatCasting'],
                    $this->settings['paramPlaceholder'],
                    $this->settings['floatCasting']
                );
            }
            elseif (strpos($value, $this->settings['wildcard']) !== false) {
                $items[] = sprintf('%s LIKE %s',
                    $this->escapeIdentifier($key),
                    $this->settings['paramPlaceholder']
                );
            }
            else {
                $items[] = sprintf('%s = %s',
                    $this->escapeIdentifier($key),
                    $this->settings['paramPlaceholder']
                );
            }
        }

        return ' WHERE ' . implode(' AND ', $items);
    }

    protected function prepareOrderBy(array $params)
    {
        if (count($params) == 0) return '';

        $items = array();
        foreach ($params as $key => $value) {
            $items[] = sprintf('%s %s',
                $this->escapeIdentifier($key),
                strtoupper($value)
            );
        }

        return ' ORDER BY ' . implode(', ', $items);
    }

    protected function prepareLimit(array $params)
    {
        if (count($params) == 0) return '';

        $items = array();
        if (isset($params['offset'])) $items[] = intval($params['offset']);
        if (isset($params['limit']))  $items[] = intval($params['limit']);
        if (!isset($params['limit'])) throw new \Exception('Key "limit" not set');

        return ' LIMIT ' . implode(', ', $items);
    }
}