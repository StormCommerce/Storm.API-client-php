<?php


namespace Storm\Model\Support;


interface MapperInterface
{
    public function build(array $attributes);
    public function service($name);
    public function get($name, $key, $default);
}