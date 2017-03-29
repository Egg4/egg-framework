<?php

namespace Egg\Yolk\Shm;

class Block
{
    protected $id;
    protected $perms;

    public function __construct($id, $perms = 0644)
    {
        $this->id = $id;
        $this->perms = $perms;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getPermissions()
    {
        return $this->perms;
    }

    public function write($data)
    {
        $this->delete();
        $size = mb_strlen($data, 'UTF-8');
        $shmid = @shmop_open($this->id, 'c', $this->perms, $size);
        shmop_write($shmid, $data, 0);
        shmop_close($shmid);
    }

    public function read()
    {
        $shmid = @shmop_open($this->id, 'a', 0, 0);
        if (!$shmid) return false;
        $data = shmop_read($shmid, 0, shmop_size($shmid));
        shmop_close($shmid);

        return $data;
    }

    public function delete()
    {
        $shmid = @shmop_open($this->id, 'w', 0, 0);
        if ($shmid) {
            shmop_delete($shmid);
            shmop_close($shmid);
        }
    }
}