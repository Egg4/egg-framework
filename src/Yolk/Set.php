<?php

namespace Egg\Yolk;

class Set extends \ArrayObject
{
    public function indexBy($key, $unique = true)
    {
        $data = array();
        foreach ($this as $item) {
            if ($unique) {
                $data[$item->$key] = $item;
            }
            else {
                $data[$item->$key][] = $item;
            }
        }
        $this->exchangeArray($data);
    }

    public function sortBy(array $keys, $direction)
    {
        if (!in_array($direction, array('asc', 'desc'))) {
            throw new \Exception('Unsupported sort "%s"', $direction);
        }

        $that = $this;
        $this->uasort(function($item1, $item2) use ($that, $keys, $direction) {
            foreach ($keys as $key) {
                $result = $that->compareByKey($item1, $item2, $key, $direction);
                if ($result != 0) return $result;
            }

            return 0;
        });
    }

    public function compareByKey($item1, $item2, $key, $direction)
    {
        if ($item1->$key == $item2->$key) return 0;
        switch($direction) {
            case 'asc': return ($item1->$key < $item2->$key) ? -1 : 1;
            case 'desc': return ($item1->$key < $item2->$key) ? 1 : -1;
            default:
                throw new \Exception('Unsupported sort "%s"', $direction);
        }
    }
}