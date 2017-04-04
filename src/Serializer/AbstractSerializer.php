<?php

namespace Egg\Serializer;

use \Egg\Yolk\Set;
use Egg\Interfaces\SerializerInterface;

abstract class AbstractSerializer implements SerializerInterface
{
    public function serialize($input) {
        if ($input instanceof Set) {
            return $this->serializeSet($input);
        }
        else {
            return $this->toArray($input);
        }
    }

    protected function serializeSet($input)
    {
        $array = array();
        foreach ($input as $item) {
            $array[] = $this->toArray($item);
        }

        return $array;
    }

    protected abstract function toArray($input);
}