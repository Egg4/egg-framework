<?php

namespace Egg\Orm\Database;

use \PHPUnit\Framework\TestCase;
use Egg\FactoryTest;
use Egg\Orm\EntitySet\Generic as EntitySet;
use Egg\Orm\Entity\Generic as Entity;

class PdoTest extends TestCase
{
    protected static $database;

    public static function setUpBeforeClass(): void
    {
        static::$database = FactoryTest::createPdoDatabase();
        static::$database->beginTransaction();
        $sql = 'CREATE TABLE `users` (
          `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
          `login` varchar(255) NOT NULL,
          `password` varchar(255) NOT NULL,
          PRIMARY KEY (`id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8;';
        static::$database->execute($sql);
    }

    public static function tearDownAfterClass(): void
    {
        $sql = 'DROP TABLE IF EXISTS `users`;';
        static::$database->execute($sql);
        static::$database->rollback();
    }

    public function testShouldInsertData()
    {
        $sql = 'INSERT INTO `users` (`id`, `login`, `password`) VALUES (?, ?, ?);';
        $params = [1, 'login', 'password'];

        $statement = static::$database->execute($sql, $params);

        $this->assertEquals(1, $statement->entityCount());
        $this->assertEquals(1, static::$database->lastInsertId());
    }

    public function testShouldSelectOneData()
    {
        $sql = 'SELECT * FROM `users` WHERE `id` = ?;';
        $params = [1];

        $statement = static::$database->execute($sql, $params);
        $entity = $statement->fetchEntity(Entity::class);

        $this->assertEquals(1, $statement->entityCount());
        $this->assertEquals(1, $entity->id);
        $this->assertEquals('login', $entity->login);
        $this->assertEquals('password', $entity->password);
    }

    public function testShouldSelectAllData()
    {
        $sql = 'SELECT * FROM `users` WHERE `id` = ?;';
        $params = [1];

        $statement = static::$database->execute($sql, $params);
        $entitySet = $statement->fetchEntitySet(EntitySet::class, Entity::class);

        $this->assertEquals(1, $statement->entityCount());
        foreach($entitySet as $entity) {
            $this->assertEquals(1, $entity->id);
            $this->assertEquals('login', $entity->login);
            $this->assertEquals('password', $entity->password);
        }
    }

    public function testShouldUpdateData()
    {
        $sql = 'UPDATE `users` SET `password` = ? WHERE `id` = ?;';
        $params = ['password2', 1];

        $statement = static::$database->execute($sql, $params);

        $this->assertEquals(1, $statement->entityCount());
    }

    public function testShouldDeleteData()
    {
        $sql = 'DELETE FROM `users` WHERE `id` = ?;';
        $params = [1];

        $statement = static::$database->execute($sql, $params);

        $this->assertEquals(1, $statement->entityCount());
    }
}