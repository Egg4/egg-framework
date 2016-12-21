<?php

namespace Egg\Yolk;

class RecursiveArrayObject extends \ArrayObject
{
	public function __construct(array $array)
	{
        parent::__construct($array, \ArrayObject::ARRAY_AS_PROPS);
		foreach($array as $key => $value) {
			$this->offsetSet($key, $value);
		}
	}

	public function offsetSet($key, $value)
	{
		if(is_array($value)) {
			$value = new static($value);
		}
		parent::offsetSet($key, $value);
	}

	public function __set($key, $value)
	{
		$this->offsetSet($key, $value);
	}

	public function offsetGet($key)
	{
		if (!$this->offsetExists($key)) {
			throw new \Exception(sprintf('%s property "%s" not found', get_class($this), $key));
		}

		return parent::offsetGet($key);
	}

	public function __get($key)
	{
		return $this->offsetGet($key);
	}

	public function toArray()
	{
		$array = $this->getArrayCopy();
		foreach($array as $key => $value) {
			if ($value instanceof self) {
				$array[$key] = $value->toArray();
			}
		}

		return $array;
	}
}