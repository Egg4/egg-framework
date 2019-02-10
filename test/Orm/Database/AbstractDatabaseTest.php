<?php

namespace Egg\Orm\Database;

use Egg\Orm\Database\Closure as ClosureDatabase;

class AbstractDatabaseTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldPrepareInsert()
    {
        $data = [
            'login'     => 'login',
            'password'  => 'password',
        ];
        $database = new ClosureDatabase(function() {});

        $sql = $database->prepareInsert('users', $data);

        $this->assertEquals('INSERT INTO `users` (`login`, `password`) VALUES (?, ?);', $sql);
    }

    public function testShouldPrepareDelete()
    {
        $where = [
            'id'     => 27,
        ];
        $database = new ClosureDatabase(function() {});

        $sql = $database->prepareDelete('users', $where);

        $this->assertEquals('DELETE FROM `users` WHERE `id` = ?;', $sql);
    }

    public function testShouldPrepareUpdate()
    {
        $data = [
            'login'     => 'login',
            'password'  => 'password',
        ];
        $where = [
            'id'     => 27,
        ];
        $database = new ClosureDatabase(function() {});

        $sql = $database->prepareUpdate('users', $data, $where);

        $this->assertEquals('UPDATE `users` SET `login` = ?, `password` = ? WHERE `id` = ?;', $sql);
    }

    public function testShouldPrepareSelect()
    {
        $where = [
            'login'     => 'login',
            'id'        => 27,
        ];
        $orderBy = [
            'login'     => 'desc',
        ];
        $limit = [
            'limit'     => 12,
        ];
        $database = new ClosureDatabase(function() {});

        $sql = $database->prepareSelect('users', $where, $orderBy, $limit);

        $this->assertEquals('SELECT * FROM `users` WHERE `login` = ? AND `id` = ? ORDER BY `login` DESC LIMIT 12;', $sql);
    }

    public function testShouldPrepareParams()
    {
        $data = [
            'id'        => [27, 32],
            'login'     => 'login',
            'password'  => '%WILDCARD%password%WILDCARD%',
            'null'      => null,
            'bool'      => true,
        ];
        $database = new ClosureDatabase(function() {});

        $params = $database->prepareParams($data);

        $this->assertEquals([27, 32, 'login', '%password%', null, 1], $params);
    }

    public function testShouldPrepareWhereIsNull()
    {
        $where = [
            'login'     => null,
        ];
        $database = new ClosureDatabase(function() {});

        $sql = $database->prepareSelect('users', $where);

        $this->assertEquals('SELECT * FROM `users` WHERE `login` IS ?;', $sql);
    }

    public function testShouldPrepareWhereIn()
    {
        $where = [
            'id'        => [27, 32],
        ];
        $database = new ClosureDatabase(function() {});

        $sql = $database->prepareSelect('users', $where);

        $this->assertEquals('SELECT * FROM `users` WHERE `id` IN (?, ?);', $sql);
    }

    public function testShouldPrepareWhereFloat()
    {
        $where = [
            'rate'        => 43.12,
        ];
        $database = new ClosureDatabase(function() {});

        $sql = $database->prepareSelect('users', $where);

        $this->assertEquals('SELECT * FROM `users` WHERE CAST(`rate` AS DECIMAL(10,5)) = CAST(? AS DECIMAL(10,5));', $sql);
    }

    public function testShouldPrepareWhereLike()
    {
        $where = [
            'password'  => '%WILDCARD%password%WILDCARD%',
        ];
        $database = new ClosureDatabase(function() {});

        $sql = $database->prepareSelect('users', $where);

        $this->assertEquals('SELECT * FROM `users` WHERE `password` LIKE ?;', $sql);
    }
}