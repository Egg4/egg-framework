<?php

namespace Egg\Orm\EntitySet;

use \Egg\Yolk\Set;
use Egg\Interfaces\EntitySetInterface;

abstract class AbstractEntitySet extends Set implements EntitySetInterface
{
    public function toArray()
    {
        $data = [];
        foreach ($this as $entity) {
            $data[] = $entity->toArray();
        }

        return $data;
    }
}