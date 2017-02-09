<?php

namespace Egg\Serializer;

use Egg\Interfaces\EntityInterface as Entity;

class Generic extends AbstractSerializer
{
    public function toArray($input) {
        return $input instanceof Entity ? $input->toArray() : $input;
    }
}