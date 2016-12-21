<?php

namespace Egg\Http;

class Body extends \Slim\Http\Body
{
    protected $content;

    public function __construct($stream, $content) {
        parent::__construct($stream);
        $this->setContent($content);
    }

    public function getContent() {
        return $this->content;
    }

    public function setContent($content) {
        $this->content = $content;
    }
}