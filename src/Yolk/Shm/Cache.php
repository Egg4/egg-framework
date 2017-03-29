<?php

namespace Egg\Yolk\Shm;

class Cache
{
    const TABLE_ID = 887;

    protected $table;

    public function __construct()
    {
        $this->table = new Block(self::TABLE_ID);
    }

    protected function readTable()
    {
        $data = $this->table->read();
        return $data ? unserialize($data) : [];
    }

    protected function writeTable($data)
    {
        $this->table->write(serialize($data));
    }

    protected function getAttr($key)
    {
        $table = $this->readTable();
        $attr = isset($table[$key]) ? $table[$key] : [
            'id'        => null,
            'timeout'   => 0,
        ];

        return array_values($attr);
    }

    protected function setAttr($key, $id, $timeout)
    {
        $table = $this->readTable();
        $table[$key] = [
            'id'        => $id,
            'timeout'   => $timeout,
        ];
        $this->writeTable($table);
    }

    protected function deleteAttr($key)
    {
        $table = $this->readTable();
        unset($table[$key]);
        $this->writeTable($table);
    }

    protected function generateId()
    {
        $table = $this->readTable();
        $ids = array_map(function($attr) {
            return $attr['id'];
        }, $table);
        $newId = count($ids) > 0 ? max($ids) + 1 : 1;

        return $newId;
    }

    public function get($key)
    {
        list($id, $timeout) = $this->getAttr($key);
        if (!$id) return false;
        if (time() > $timeout) {
            $this->delete($key);
            return false;
        }

        $block = new Block($id);
        $data = $block->read();

        return $data !== false ? unserialize($data) : false;
    }

    public function set($key, $data, $ttl)
    {
        list($id) = $this->getAttr($key);
        if (!$id) {
            $id = $this->generateId();
        }

        $timeout = time() + $ttl;
        $this->setAttr($key, $id, $timeout);
        $block = new Block($id);
        $block->write(serialize($data));
    }

    public function delete($key)
    {
        list($id) = $this->getAttr($key);
        if (!$id) return false;

        $this->deleteAttr($key);
        $block = new Block($id);
        $block->delete();
    }

    public function clear()
    {
        $table = $this->readTable();
        $this->writeTable([]);

        foreach($table as $attr) {
            $block = new Block($attr['id']);
            $block->delete();
        }
    }
}