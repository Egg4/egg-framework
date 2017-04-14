<?php

namespace Egg\Orm\Repository;

use Egg\Interfaces\RepositoryInterface;

abstract class AbstractRepository implements RepositoryInterface
{
    protected $settings;

    public function __construct(array $settings = [])
    {
        $this->settings = array_merge([
            'container' => null,
        ], $settings);
    }

    public function __call($table, $args)
    {
        $matches = array(
            'deleteBy',
            'updateBy',
            'selectOneBy',
            'selectAllBy',
        );

        foreach ($matches as $match) {
            if (strpos($table, $match) === 0) {
                $method = substr($table, 0, strpos($table, 'By'));
                $field = \Egg\Yolk\String::underscore(substr($table, strlen($match)));
                if (!isset($args[0])) {
                    throw new \Exception(sprintf('Argument 1 of "%s" expected', $table));
                }
                if ($method != 'update') {
                    return call_user_func_array([$this, $method], [[$field => $args[0]]]);
                }
                if (!isset($args[1])) {
                    throw new \Exception(sprintf('Argument 2 of "%s" expected', $table));
                }
                return call_user_func_array([$this, $method], [$args[0], [$field => $args[1]]]);
            }
        }

        throw new \Exception(sprintf('Method "%s" not found', $table));
    }
}