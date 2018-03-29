<?php


namespace Storm\Model\Support;


use Traversable;

abstract class BaseCollection implements \ArrayAccess, \IteratorAggregate, Arrayable
{
    /**
     * @var array
     */
    protected $items = [];

    /**
     * @param mixed $key
     * @return bool
     */
    public function offsetExists($key)
    {
        return array_key_exists($key, $this->items);
    }

    /**
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->items[$offset];
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->items[$offset]);
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->items);
    }

    public function toArray()
    {
        return array_map(function ($value) {
            return $value instanceof Arrayable ? $value->toArray() : $value;

        }, $this->items);
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }

    public function toJson($options = 0)
    {
        return json_encode($this->jsonSerialize(),$options);
    }
}