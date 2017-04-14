<?php

namespace Egg\Orm\Repository;

use Egg\Container;
use Egg\Orm\Database\Closure as ClosureDatabase;
use Egg\Orm\Statement\Closure as ClosureStatement;
use Egg\Orm\Repository\Generic as GenericRepository;
use Egg\Orm\EntitySet\Generic as EntitySet;
use Egg\Orm\Entity\Generic as Entity;

class GenericTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldInsertData()
    {
        $data = [
            'login'     => 'login',
            'password'  => 'password',
        ];
        $container = new Container([
            'database' => new ClosureDatabase(function($action, $arguments = []) {
                switch ($action) {
                    case 'execute':
                        list($sql, $params) = $arguments;
                        $this->assertEquals('INSERT INTO `users` (`login`, `password`) VALUES (?, ?);', $sql);
                        $this->assertEquals(['login', 'password'], $params);
                        return null;
                    case 'lastInsertId':
                        return 1;
                }
            })
        ]);

        $repository = new GenericRepository([
            'container' => $container,
            'resource'  => 'users',
        ]);
        $id = $repository->insert($data);

        $this->assertEquals(1, $id);
    }

    public function testShouldDeleteData()
    {
        $where = [
            'id'     => 27,
        ];
        $container = new Container([
            'database' => new ClosureDatabase(function($action, $arguments = []) {
                $this->assertEquals('execute', $action);
                list($sql, $params) = $arguments;
                $this->assertEquals('DELETE FROM `users` WHERE `id` = ?;', $sql);
                $this->assertEquals([27], $params);
                return new ClosureStatement(function($action) {
                    $this->assertEquals('entityCount', $action);
                    return 1;
                });
            })
        ]);

        $repository = new GenericRepository([
            'container' => $container,
            'resource'  => 'users',
        ]);
        $count = $repository->delete($where);

        $this->assertEquals(1, $count);
    }

    public function testShouldUpdateData()
    {
        $data = [
            'login'     => 'login',
            'password'  => 'password',
        ];
        $where = [
            'id'     => 27,
        ];
        $container = new Container([
            'database' => new ClosureDatabase(function($action, $arguments = []) {
                $this->assertEquals('execute', $action);
                list($sql, $params) = $arguments;
                $this->assertEquals('UPDATE `users` SET `login` = ?, `password` = ? WHERE `id` = ?;', $sql);
                $this->assertEquals(['login', 'password', 27], $params);
                return new ClosureStatement(function($action) {
                    $this->assertEquals('entityCount', $action);
                    return 1;
                });
            })
        ]);

        $repository = new GenericRepository([
            'container' => $container,
            'resource'  => 'users',
        ]);
        $count = $repository->update($data, $where);

        $this->assertEquals(1, $count);
    }

    public function testShouldSelectAllData()
    {
        $where = [
            'id'     => 27,
        ];
        $container = new Container([
            'database' => new ClosureDatabase(function($action, $arguments = []) {
                $this->assertEquals('execute', $action);
                list($sql, $params) = $arguments;
                $this->assertEquals('SELECT * FROM `users` WHERE `id` = ?;', $sql);
                $this->assertEquals([27], $params);
                return new ClosureStatement(function($action) {
                    $this->assertEquals('fetchEntitySet', $action);
                    return new EntitySet();
                });
            })
        ]);

        $repository = new GenericRepository([
            'container' => $container,
            'resource'  => 'users',
        ]);
        $entitySet = $repository->selectAll($where);

        $this->assertEquals(EntitySet::class, get_class($entitySet));
    }

    public function testShouldSelectOneData()
    {
        $where = [
            'id'     => 27,
        ];
        $container = new Container([
            'database' => new ClosureDatabase(function($action, $arguments = []) {
                $this->assertEquals('execute', $action);
                list($sql, $params) = $arguments;
                $this->assertEquals('SELECT * FROM `users` WHERE `id` = ? LIMIT 1;', $sql);
                $this->assertEquals([27], $params);
                return new ClosureStatement(function($action) {
                    $this->assertEquals('fetchEntity', $action);
                    return new Entity();
                });
            })
        ]);

        $repository = new GenericRepository([
            'container' => $container,
            'resource'  => 'users',
        ]);
        $entity = $repository->selectOne($where);

        $this->assertEquals(Entity::class, get_class($entity));
    }
}