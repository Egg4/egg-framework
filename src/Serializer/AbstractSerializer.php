<?php

namespace Egg\Serializer;

use Egg\Interfaces\SerializerInterface;

abstract class AbstractSerializer implements SerializerInterface
{
    public function serialize($input) {
        if ($input instanceof \Traversable OR is_array($input)) {
            return $this->serializeTraversable($input);
        }
        else {
            return $this->toArray($input);
        }
    }

    protected function serializeTraversable($input)
    {
        $array = array();
        foreach ($input as $item) {
            $array[] = $this->toArray($item);
        }

        return $array;
    }

    protected abstract function toArray($input);
}