<?php
namespace Storm\Model\Support;

use Carbon\Carbon;
use Storm\Model\StormItem;
use Storm\Proxy\AbstractProxy;
use Storm\StormClient;
use Storm\Util\Arr;
use Storm\Util\Str;

class StormModel implements Arrayable
{
    protected $key = "";
    protected $attributes = [];
    protected $collection;
    protected $cacheKey;
    protected $entity;
    protected $resolver;
    protected $appends = [];

    /**
     * StormModel constructor.
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
    }

    public function __get($name)
    {
        if (in_array($name, $this->appends)) {
            return call_user_func([$this, "get$name"]);
        } else {
            return $this->getAttribute($name);
        }
    }

    function __set($name, $value)
    {
        $this->setAttribute($name, $value);
    }

    public function fill($attributes)
    {
        $this->attributes = $attributes;
        $keys = $attributes;
        if ($this->resolver() !== null) {
            if (!empty($this->entity())) {
                $keys = array_diff($this->entity()['Properties'], ['string', 'string?', 'int', 'int?', 'Guid', 'decimal', 'bool']); // get complex types
            }
            foreach ($keys as $key => $type) {
                if (isset($attributes[$key])) {
                    if ($type == "DateTime") {
                        if(is_string($attributes[$key])) {
                            $attributes[$key] = $this->formatDate($attributes[$key]);
                        }
                    }
                    $this->setAttribute($key, $attributes[$key]);
                }
            }
        }
        return $this;
    }

    public function formatDate($value)
    {

        preg_match('/(\d{10})(\d{3})([\+\-]\d{4})/', $value, $matches);
        return Carbon::createFromFormat("U.u.O",vsprintf('%2$s.%3$s.%4$s', $matches));
    }

    public function collection()
    {
        if ($this->collection == null) {
            $this->collection = new Collection();
        }
        return $this->collection;
    }

    public function postData()
    {


        if (!empty($this->entity()['Properties'])) {
            $properties = $this->entity()['Properties'];
            $data = [];
            $attributes = $this->toArray();
            foreach ($properties as $key => $value) {
                if (isset($attributes[$key])) {
                    $data[$key] = $attributes[$key];
                } else {
                    $data[$key] = null;
                }
            }
            foreach ($this->entity()['Properties'] as $key => $value) { // Only return values that actually belongs to the model
                if (!is_array($data[$key]) && strlen($data[$key]) === 0) {
                    if ($value === "int") {
                        $data[$key] = 0;
                    } elseif ($value === "decimal") {
                        $data[$key] = 0;
                    } elseif ($value === "bool") {
                        $data[$key] = false;
                    } elseif ($value === "string") {
                        $data[$key] = "";
                    } elseif ($value === "Guid") {
                        $data[$key] = "00000000-0000-0000-0000-000000000000";
                    } elseif (Str::contains("?", $value, false)) {
                        $data[$key] = null;
                    }
                }
            }
        } else {
            $data = $this->toArray();
        }
        return $data;
    }

    public function has($key)
    {
        return isset($this->attributes[$key]) && strlen($this->attributes[$key]) > 0;
    }

    public function get($key)
    {
        return $this->attributes[$key];
    }

    /**
     * SetAttributes is the most called method in the library and changes should be done with caution
     * Use a profiler to determine if your change is ok
     *
     * @param $key
     * @param string $value
     * @return $this
     */
    public function setAttribute($key, $value = "")
    {

        if (is_array($value)) {
            $ent = $this->entity();
            if (is_array($ent) && isset($ent['Properties'][$key])) {

                $value = $this->resolver()->resolve($value, $ent['Properties'][$key]);
            }
            if ($value instanceof StormModel) {
                $value->setKey($key);
            }
        }

        $this->attributes[$key] = $value;
        return $this;
    }

    public function entity()
    {
        if ($this->entity == null) {
            $this->entity = $this->resolver()->entity($this->getName());
            if (empty($this->entity)) {
                $this->resolver = StormClient::self()->proxies()->findResolver($this->getName());
                $this->entity = $this->resolver()->entity($this->getName());
            }
        }
        return $this->entity;
    }

    public function getName()
    {
        return (new \ReflectionClass($this))->getShortName();
    }

    public function getAttribute($key, $default = "")
    {
        $value = $default;
        if (isset($this->attributes[$key])) {
            $value = $this->attributes[$key];
        }
        return $value;
    }

    public function setKey($value)
    {
        $this->key = $value;
    }

    public function getEnt()
    {
        return StormClient::self()->entitiesMapper()->entity((new \ReflectionClass($this))->getShortName());
    }

    public function key()
    {
        return $this->key;
    }

    public function toArray()
    {
        $attributes = $this->append($this->attributes);
        return array_map(function ($value) {
            return $value instanceof Arrayable ? $value->toArray() : $value;
        }, $attributes);
    }

    public function toJson()
    {
        return json_encode($this->prepareJson($this->toArray()));
    }

    public function prepareJson($array)
    {
        $array = Arr::replace('\"', '"', $array); // Simply to ensure no double escaping resulting in \\"
        $array = Arr::replace("\r", '\\r', $array);
        $array = Arr::replace("\n", '\\n', $array);
        $array = Arr::replace("\r\n", '\\r\\n', $array);
        return Arr::replace('"', '\"', $array);
    }

    public function is($key)
    {
        if ($this instanceof StormItem) {
            return $this->getName() == $key;
        } else {
            return ($this instanceof $key);
        }
    }

    /**
     * @param StormModel $model
     */
    public function merge(StormModel $model)
    {
        $attributes = $model->attributes;
        if (!empty($this->entity())) {
            $keys = $this->entity()['Properties']; // get complex types
        }
        foreach ($keys as $key => $type) {
            if (isset($attributes[$key])) {
                if ($attributes[$key] instanceof StormModel) {
                    if ($this->has($key)) {
                        $this->get($key)->merge($attributes[$key]);
                    } else {
                        $this->setAttribute($key, $attributes[$key]);
                    }
                } else {
                    $this->setAttribute($key, $attributes[$key]);
                }
            }
        }
        return $this;
    }

    /**
     * @return mixed|Resolver
     */
    public function resolver()
    {
        if ($this->resolver == null) {
            $this->resolver = StormClient::self()->proxies()->findResolver($this->getName());
            if ($this->resolver == null) {
                $this->resolver = AbstractProxy::latestResolver();
            }
        }
        return $this->resolver;
    }

    public function __toString()
    {
        return "" . json_encode($this->toArray());
    }

    public function append($attributes)
    {
        foreach ($this->appends as $append) {
            $attributes[$append] = call_user_func([$this, "get$append"]);
        }
        return $attributes;
    }
}
