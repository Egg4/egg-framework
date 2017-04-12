<?php

namespace Egg\Orm\Factory;

use Egg\FactoryTest;
use Egg\Orm\Factory\Generic as GenericFactory;

class GenericTest extends \PHPUnit\Framework\TestCase
{
    protected static $database;
    protected static $schema;
    protected static $repositories;

    public static function setUpBeforeClass()
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

        $sql = 'CREATE TABLE `houses` (
          `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
          `user_id` int(10) UNSIGNED NOT NULL,
          `name` varchar(255) NOT NULL,
          PRIMARY KEY (`id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8;';
        static::$database->execute($sql);

        $sql = 'ALTER TABLE `houses`
          ADD KEY `user_id` (`user_id`),
          ADD UNIQUE KEY `unique` (`user_id`, `name`);';
        static::$database->execute($sql);

        $sql = 'ALTER TABLE `houses`
          ADD CONSTRAINT `houses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;';
        static::$database->execute($sql);

        static::$schema = new \Egg\Orm\Schema\Mysql(['database' => static::$database]);
        static::$repositories = [
            'users' => new \Egg\Orm\Repository\Generic([
                'database'  => static::$database,
                'table'     => 'users',
            ]),
            'houses' => new \Egg\Orm\Repository\Generic([
                'database'  => static::$database,
                'table'     => 'houses',
            ]),
        ];
    }

    public static function tearDownAfterClass()
    {
        $sql = 'DROP TABLE IF EXISTS `houses`;';
        static::$database->execute($sql);
        $sql = 'DROP TABLE IF EXISTS `users`;';
        static::$database->execute($sql);
        static::$database->rollback();
    }

    public function testShouldCreateUser()
    {
        $factory = new GenericFactory([
            'schema'        => static::$schema,
            'table'         => 'users',
            'repositories'  => static::$repositories,
        ]);

        $user = $factory->create(['id' => 1, 'login' => 'user1']);

        $this->assertEquals(1, $user->id);
        $this->assertEquals('user1', $user->login);
        $this->assertEquals(32, strlen($user->password));
    }

    public function testShouldCreateHouse()
    {
        $factory = new GenericFactory([
            'schema'        => static::$schema,
            'table'         => 'houses',
            'repositories'  => static::$repositories,
        ]);

        $house = $factory->create(['user_id' => 1]);

        $this->assertTrue(is_numeric($house->id));
        $this->assertEquals(1, $house->user_id);
        $this->assertEquals(32, strlen($house->name));
    }

    public function testShouldCreateUserAndHouse()
    {
        $factory = new GenericFactory([
            'schema'        => static::$schema,
            'table'         => 'houses',
            'repositories'  => static::$repositories,
        ]);

        $house = $factory->create();

        $this->assertTrue(is_numeric($house->id));
        $this->assertTrue(is_numeric($house->user_id));
        $this->assertEquals(32, strlen($house->name));

        $user = static::$repositories['users']->selectOneById($house->user_id);
        $this->assertTrue(is_numeric($user->id));
        $this->assertEquals(32, strlen($user->login));
        $this->assertEquals(32, strlen($user->password));
    }
}