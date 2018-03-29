<?php


namespace Storm\Model;


use Storm\StormClient;

class Callback
{
    protected $attributes;

    public function __construct($attributes)
    {
        $this->attributes = $attributes;
    }

    public function nameValues()
    {
        $nameValues = [];
        foreach ($this->attributes as $key => $value) {
            $nameValues[] = new StormItem( [
                'Name' => $key,
                'Value' => $value
            ],'NameValue');
        }
        return new StormItem($nameValues,'NameValues');
    }

    public function get($key, $default = "")
    {
        return isset($this->attributes[$key]) ? $this->attributes[$key] : $default;
    }

    public function has($key)
    {
        return isset($this->attributes[$key]);
    }
}