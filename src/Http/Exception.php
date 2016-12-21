<?php

namespace Egg\Http;

class Exception extends \Exception
{
    protected $errors = [];

    public function __construct($status, Error $error = null)
    {
        parent::__construct('', $status);
        if ($error) $this->addError($error);
    }

    public function getStatus()
    {
        return $this->code;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function hasErrors()
    {
        return count($this->errors) > 0;
    }

    public function addError(Error $error)
    {
        $this->errors[] = $error;
        if (count($this->errors) == 1) {
            $this->message = $this->errors[0]->getDescription();
        }
    }

    public function addErrors(array $errors)
    {
        $this->errors = array_merge($this->errors, $errors);
    }
}