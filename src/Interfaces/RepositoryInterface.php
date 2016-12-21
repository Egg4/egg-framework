<?php

namespace Egg\Interfaces;

interface RepositoryInterface
{
    public function getDatabase();
    public function insert(array $data);
    public function delete(array $where = []);
    public function update(array $data, array $where = []);
    public function selectAll(array $where = [], array $orderBy = [], array $limit = []);
    public function selectOne(array $where = []);
}