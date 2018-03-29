<?php


namespace Storm\Model\Support;


/**
 * Class Collection
 * @package Storm\Model\Support
 */
class Collection extends BaseCollection
{

    /**
     * Collection constructor.
     * @param array $items
     */
    public function __construct($items = [])
    {
        $this->items = $items;
    }

    /**
     * @param $item
     */
    public function add($item)
    {
        $this->offsetSet(null, $item);
        return $this;
    }

    /**
     *
     */
    public function rewind()
    {
        reset($this->items);
    }

    /**
     * @param $key
     * @param $item
     */
    public function put($key, $item)
    {
        $this->offsetSet($key, $item);
    }

    /**
     * @param $key
     * @param null $default
     * @return null
     */
    public function get($key, $default = null)
    {
        $value = $default;
        if ($this->has($key)) {
            $value = $this->offsetGet($key);
        }
        return $value;
    }

    /**
     * @param $key
     * @return bool
     */
    public function has($key)
    {
        return $this->offsetExists($key);

    }

    /**
     * @param array $mergeWith
     * @return $this
     */
    public function merge($mergeWith = [])
    {
        if ($mergeWith instanceof Collection) {
            $mergeWith = $mergeWith->values();
        }
        $this->items = array_merge($this->items, $mergeWith);
        return $this;
    }

    /**
     * @return array
     */
    public function values()
    {
        return array_values($this->items);
    }

    /**
     * @param null $default
     * @return mixed|null
     */
    public function first($default = null)
    {
        if ($this->count() == 0) {
            return $default;
        } else {
            return reset($this->items);
        }
    }

    /**
     * @param null $default
     * @return mixed|null
     */
    public function last($default = null)
    {
        if ($this->count() == 0) {
            return $default;
        } else {
            return end($this->items);
        }
    }

    /**
     * @param $items
     * @return $this
     */
    public function append($items)
    {
        foreach ($items as $item) {
            $this->add($item);
        }
        return $this;
    }

    /**
     * Returns an array of $key member on $items
     * e.g. $collection->value('id') returns an array of ids if collection members has an id attribute
     * @param $key
     * @return array
     */
    public function value($key)
    {
        $values = [];
        foreach ($this->values() as $item) {
            if (is_array($item) && isset($item[$key])) {
                $values[] = $item[$key];
            } elseif (method_exists($item, 'get')) {
                $values[] = $item->get($key);
            } else {
                $values[] = $item->$key;
            }
        }
        return $values;
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->items);
    }

    /**
     * @param $key
     * @return $this
     */
    public function remove($key)
    {
        $this->offsetUnset($key);
        return $this;
    }

    /**
     * @param callable|null $callback
     * @return static
     */
    public function filter(callable $callback = null)
    {
        if ($callback) {
            $return = [];
            foreach ($this->items as $key => $value) {
                if ($callback($value, $key)) {
                    $return[$key] = $value;
                }
            }
            return new static($return);
        }
        return new static(array_filter($this->items));
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return count($this->items) == 0;
    }

    /**
     * @return $this
     */
    public function shuffle()
    {
        shuffle($this->items);
        return $this;
    }
    public function reverse() {
        $this->items = array_reverse($this->items,true);
    }
    /**
     * @param int $amount
     * @return Collection
     */
    public function take($amount = 0)
    {
        return new Collection(array_slice($this->items, 0, $amount, false));
    }

    /**
     * @param $key
     * @return array
     */
    public function lists($key)
    {
        $list = [];
        foreach ($this->items as $item) {
            if($item instanceof StormModel) {
                $list[] =$item->$key;
            }
        }
        return $list;
    }

    /**
     * @param $size
     * @return Collection
     */
    public function chunk($size)
    {
        $chunks = [];
        foreach (array_chunk($this->items, $size, true) as $chunk) {
            $chunks[] = new static($chunk);
        }
        return new static($chunks);
    }
}