<?php


namespace Storm\Middleware;


class AbstractMiddleware implements IMiddleware
{
    protected $data;

    public function handle($args)
    {
        return $args;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function getData($data)
    {
        return $this->data;
    }
}