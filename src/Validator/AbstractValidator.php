<?php

namespace Egg\Validator;

use Egg\Interfaces\ValidatorInterface;
use Egg\Exception\InvalidContent as InvalidContentException;
use Egg\Exception\NotFound as NotFoundException;
use Egg\Exception\NotUnique as NotUniqueException;

abstract class AbstractValidator implements ValidatorInterface
{
    protected $settings;
    protected $exception;

    public function __construct(array $settings = [])
    {
        $this->settings = array_merge([
            'container' => null,
        ], $settings);
    }

    public function validate($action, array $arguments = [])
    {
        $this->exception = new \Egg\Http\Exception($this->container['response'], 400);

        try {
            call_user_func_array([$this, $this->getMethod($action)], $arguments);
        }
        catch (InvalidContentException $exception) {
            $this->exception->addError(new \Egg\Http\Error(array(
                'name'          => 'invalid_content',
                'description'   => $exception->getMessage(),
            )));
        }
        catch (NotFoundException $exception) {
            $this->exception->addError(new \Egg\Http\Error(array(
                'name'          => 'not_found',
                'description'   => $exception->getMessage(),
            )));
        }
        catch (NotUniqueException $exception) {
            $this->exception->addError(new \Egg\Http\Error(array(
                'name'          => 'not_unique',
                'description'   => $exception->getMessage(),
            )));
        }

        if ($this->exception->hasErrors()) {
            throw $this->exception;
        }
    }

    protected function getMethod($action)
    {
        $version = $this->container['request']->getAttribute('version');
        if ($version) {
            $method = $version . '_' . $action;
            if (method_exists($this, $method)) {
                return $method;
            }
        }

        return $action;
    }
}