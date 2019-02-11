<?php

namespace Egg\Cache;

use Egg\Cache\File as FileCache;

class FileTest extends \Egg\Test
{
    protected static $cache;

    public static function setUpBeforeClass()
    {
        static::$cache = new FileCache([
            'dir'       => sys_get_temp_dir(),
            'namespace' => 'egg-framework',
        ]);
    }

    public function testShouldWrite()
    {
        $data = [
            'id' => 27,
            'name' => 'felix',
        ];

        static::$cache->set('key1', $data);
        static::$cache->set('key2', $data);
        $this->assertEquals($data, static::$cache->get('key1'));
    }

    public function testShouldRead()
    {
        $data = [
            'id' => 27,
            'name' => 'felix',
        ];

        $this->assertEquals($data, static::$cache->get('key1'));
    }

    public function testShouldDelete()
    {
        $data = [
            'id' => 27,
            'name' => 'felix',
        ];

        static::$cache->set('key1', $data);
        static::$cache->delete('key1');
        $this->assertEquals(false, static::$cache->get('key1'));
    }

    public function testShouldClear()
    {
        $data = [
            'id' => 27,
            'name' => 'felix',
        ];

        static::$cache->set('key1', $data);
        static::$cache->clear();
        $this->assertEquals(false, static::$cache->get('key1'));
    }
}