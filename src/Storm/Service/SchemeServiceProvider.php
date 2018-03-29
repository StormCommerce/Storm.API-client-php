<?php


namespace Storm\Service;


use Storm\Task\Scheme;

class SchemeServiceProvider extends StormServiceProvider
{
    protected $provides = [
        'Scheme'
    ];
    public function register()
    {
        $this->add('Scheme',Scheme::class,true);
    }
}