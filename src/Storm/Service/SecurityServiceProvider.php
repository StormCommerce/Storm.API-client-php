<?php


namespace Storm\Service;


use Storm\Security\Encrypt;
use Storm\Security\RateLimit;
use Storm\StormClient;

class SecurityServiceProvider extends StormServiceProvider
{

    protected $provides = [
        'Encrypt',
        'RateLimit'
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

        $this->add('Encrypt', Encrypt::class, true)->withArgument(StormClient::self()->get('app_key'));
        $this->add('RateLimit', RateLimit::class, true);
    }
}