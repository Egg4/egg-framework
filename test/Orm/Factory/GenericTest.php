<?php

namespace Egg\Orm\Factory;

class GenericTest extends \PHPUnit\Framework\TestCase
{
    protected static $container;

    public static function setUpBeforeClass()
    {
        static::$container = new \Egg\Container();
        static::$container['cache'] = new \Egg\Cache\Memory();

        $database = \Egg\FactoryTest::createPdoDatabase();

        $database->beginTransaction();
        $sql = 'CREATE TABLE `users` (
          `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
          `login` varchar(255) NOT NULL,
          `password` varchar(255) NOT NULL,
          PRIMARY KEY (`id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8;';
        $database->execute($sql);

        $sql = 'CREATE TABLE `houses` (
          `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
          `user_id` int(10) UNSIGNED NOT NULL,
          `name` varchar(255) NOT NULL,
          PRIMARY KEY (`id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8;';
        $database->execute($sql);

        $sql = 'ALTER TABLE `houses`
          ADD KEY `user_id` (`user_id`),
          ADD UNIQUE KEY `unique` (`user_id`, `name`);';
        $database->execute($sql);

        $sql = 'ALTER TABLE `houses`
          ADD CONSTRAINT `houses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;';
        $database->execute($sql);

        static::$container['database'] = $database;
        static::$container['schema'] = new \Egg\Orm\Schema\Mysql([
            'database'      => static::$container['database'],
            'cache'         => static::$container['cache'],
        ]);
        static::$container['repository'] = [
            'users' => new \Egg\Orm\Repository\Generic([
                'container' => static::$container,
                'resource'  => 'users',
            ]),
            'houses' => new \Egg\Orm\Repository\Generic([
                'container' => static::$container,
                'resource'  => 'houses',
            ]),
        ];
        static::$container['factory'] = [
            'users' => new \Egg\Orm\Factory\Generic([
                'container' => static::$container,
                'resource'  => 'users',
            ]),
            'houses' => new \Egg\Orm\Factory\Generic([
                'container' => static::$container,
                'resource'  => 'houses',
            ]),
        ];
    }

    public static function tearDownAfterClass()
    {
        $sql = 'DROP TABLE IF EXISTS `houses`;';
        static::$container['database']->execute($sql);
        $sql = 'DROP TABLE IF EXISTS `users`;';
        static::$container['database']->execute($sql);
        static::$container['database']->rollback();
    }

    public function testShouldCreateUser()
    {
        $factory = static::$container['factory']['users'];
        $user = $factory->create(['id' => 1, 'login' => 'user1']);

        $this->assertEquals(1, $user->id);
        $this->assertEquals('user1', $user->login);
        $this->assertEquals(32, strlen($user->password));
    }

    public function testShouldCreateHouse()
    {
        $factory = static::$container['factory']['houses'];
        $house = $factory->create(['user_id' => 1]);

        $this->assertTrue(is_numeric($house->id));
        $this->assertEquals(1, $house->user_id);
        $this->assertEquals(32, strlen($house->name));
    }

    public function testShouldCreateUserAndHouse()
    {
        $factory = static::$container['factory']['houses'];
        $house = $factory->create();

        $this->assertTrue(is_numeric($house->id));
        $this->assertTrue(is_numeric($house->user_id));
        $this->assertEquals(32, strlen($house->name));

        $user = static::$container['repository']['users']->selectOneById($house->user_id);
        $this->assertTrue(is_numeric($user->id));
        $this->assertEquals(32, strlen($user->login));
        $this->assertEquals(32, strlen($user->password));
    }
}