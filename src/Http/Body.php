<?php

namespace Egg\Http;

class Body extends \Slim\Http\Body
{
    protected $content;

    public function __construct($stream, $content = null) {
        parent::__construct($stream);
        $this->setContent($content);
    }

    public function getContent() {
        if (is_null($this->content)) {
            $this->content = $this->getContents();
        }
        return $this->content;
    }

    public function setContent($content) {
        $this->content = $content;
    }
}
