<?php

namespace Egg\Cache;

use Egg\Cache\Memory as MemoryCache;

class MemoryTest extends \Egg\Test
{
    protected static $cache;

    public static function setUpBeforeClass()
    {
        static::$cache = new MemoryCache();
    }

    public function testShouldWrite()
    {
        $data = [
            'id' => 27,
            'name' => 'felix',
        ];

        static::$cache->set('key1', $data);
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