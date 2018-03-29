<?php


namespace Storm\Service;


use Storm\Http\Batcher;

class BatchServiceProvider extends StormServiceProvider
{
    protected $provides = [
        'Batcher'
    ];
    public function register()
    {
        $this->add('Batcher',Batcher::class,true)->withArgument('AccessClient');
    }

}