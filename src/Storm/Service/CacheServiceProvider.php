<?php


namespace Storm\Service;


use Storm\Cache\RedisCache;
use Predis\Client;
use Storm\StormClient;

class CacheServiceProvider extends StormServiceProvider
{
    protected $provides = [
        'Redis',
        'RedisCache'
    ];

    /**
     * Use the register method to register items with the container via the
     * protected $this->container property or the `getContainer` method
     * from the ContainerAwareTrait.
     *
     * @return void
     */
    public function register()
    {
        $this->add('Redis', Client::class)->withArgument(StormClient::self()->get('redis'));
        $this->add('RedisCache', RedisCache::class,true)->withArgument('Redis');
    }
}