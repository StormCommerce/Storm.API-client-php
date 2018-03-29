<?php


namespace Storm\Service;


use Storm\Model\Support\Cart;
use Storm\Model\Support\Customer;

class CustomerServiceProvider extends StormServiceProvider
{
    protected $provides = [
        'Customer',
        'Cart'
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
        $this->add('Customer', Customer::class, true);
        $this->add('Cart', Cart::class, true);
    }
}