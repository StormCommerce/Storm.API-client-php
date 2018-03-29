<?php


namespace Storm\Middleware;


interface IMiddleware
{
    public function handle($args);
    public function setData($data);
}