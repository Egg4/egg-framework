<?php

namespace Egg\Interfaces;

interface DatabaseInterface
{
    public function execute($sql, array $params = []);
    public function lastInsertId();
    public function beginTransaction();
    public function commit();
    public function rollback();
}