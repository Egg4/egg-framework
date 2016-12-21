<?php

namespace Egg\Http;

class Error
{
    protected $name;
    protected $description;
    protected $uri;

    public function __construct(array $data)
    {
        $this->name =           isset($data['name']) ? $data['name'] : 'unknown';
        $this->description =    isset($data['description']) ? $data['description'] : '';
        $this->uri =            isset($data['uri']) ? $data['uri'] : '';
    }

    public function getName()
    {
        return $this->name;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getUri()
    {
        return $this->uri;
    }
}