<?php

namespace Egg\Orm\EntitySet;

use \Egg\Yolk\Set;
use Egg\Interfaces\EntitySetInterface;

abstract class AbstractEntitySet extends Set implements EntitySetInterface
{
    public function toArray($key = null)
    {
        $data = [];

        if (!is_null($key)) {
            foreach ($this as $entity) {
                $data[] = $entity->get($key);
            }
        }
        else {
            foreach ($this as $entity) {
                $data[] = $entity->toArray();
            }
        }

        return $data;
    }
}