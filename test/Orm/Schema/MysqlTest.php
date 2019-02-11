<?php

namespace Egg\Orm\Schema;

use Egg\FactoryTest;

class MysqlTest extends \PHPUnit\Framework\TestCase
{
    protected static $database;

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
    }

    public static function tearDownAfterClass()
    {
        $sql = 'DROP TABLE IF EXISTS `houses`;';
        static::$database->execute($sql);
        $sql = 'DROP TABLE IF EXISTS `users`;';
        static::$database->execute($sql);
        static::$database->rollback();
    }

    public function testShouldGetSchema()
    {
        $mysqlSchema = new \Egg\Orm\Schema\Mysql([
            'database'  => static::$database,
            'cache'     => new \Egg\Cache\Memory(),
        ]);
        $schema = $mysqlSchema->getData();

        $this->assertEquals('test', $schema->name);
        $this->assertEquals(['houses', 'users'], array_keys($schema->tables));

        $userTable = $schema->tables['users'];
        $this->assertEquals(['id', 'login', 'password'], array_keys($userTable->columns));

        $idColumn = $userTable->columns['id'];
        $this->assertEquals('integer', $idColumn->type);
        $this->assertEquals(true, $idColumn->primary);
        $this->assertEquals(false, $idColumn->nullable);
        $this->assertEquals(null, $idColumn->default);
        $this->assertEquals(true, $idColumn->unsigned);
        $this->assertEquals(true, $idColumn->auto_increment);
        $this->assertEquals(null, $idColumn->max_length);

        $loginColumn = $userTable->columns['login'];
        $this->assertEquals('string', $loginColumn->type);
        $this->assertEquals(false, $loginColumn->primary);
        $this->assertEquals(false, $loginColumn->nullable);
        $this->assertEquals(null, $loginColumn->default);
        $this->assertEquals(false, $loginColumn->unsigned);
        $this->assertEquals(false, $loginColumn->auto_increment);
        $this->assertEquals(255, $loginColumn->max_length);

        $houseTable = $schema->tables['houses'];
        $this->assertEquals(['id', 'user_id', 'name'], array_keys($houseTable->columns));

        $this->assertEquals(['unique'], array_keys($houseTable->unique_keys));
        $uniqueKey = $houseTable->unique_keys['unique'];
        $this->assertEquals(['user_id', 'name'], array_keys($uniqueKey->columns));

        $foreignKey = $houseTable->foreign_keys['houses_ibfk_1'];
        $this->assertEquals('houses', $foreignKey->column->table->name);
        $this->assertEquals('user_id', $foreignKey->column->name);
        $this->assertEquals('users', $foreignKey->foreign_column->table->name);
        $this->assertEquals('id', $foreignKey->foreign_column->name);

        $userIdColumn = $houseTable->columns['user_id'];
        $this->assertEquals('houses_ibfk_1', $userIdColumn->foreign_key->name);
    }
}