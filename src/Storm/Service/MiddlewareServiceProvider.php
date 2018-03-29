<?php


namespace Storm\Service;


use Storm\Middleware\MiddlewareContainer;

class MiddlewareServiceProvider extends StormServiceProvider
{
    protected $provides = ["MiddlewareContainer"];

    public function register()
    {
        $this->add('MiddlewareContainer', MiddlewareContainer::class, true);
    }

}