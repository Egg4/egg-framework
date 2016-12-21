<?php

namespace Egg\Interfaces;

interface StatementInterface
{
    public function entityCount();
    public function fetchEntitySet($entitySetClass, $entityClass);
    public function fetchEntity($entityClass);
}