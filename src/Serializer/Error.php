<?php

namespace Egg\Serializer;

class Error extends AbstractSerializer
{
    public function toArray($error) {
        return [
            'name'        => $error->getName(),
            'description' => $error->getDescription(),
            'uri'         => $error->getUri(),
        ];
    }
}