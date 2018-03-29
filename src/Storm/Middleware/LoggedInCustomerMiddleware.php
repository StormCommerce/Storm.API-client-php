<?php


namespace Storm\Middleware;


use Storm\Model\Customer;
use Storm\StormClient;

class LoggedInCustomerMiddleware extends AbstractMiddleware
{
    /**
     * @param $args Customer
     * @return mixed
     */
    public function handle($args)
    {
        $cart = StormClient::self()->cart();
        if($cart->basketCreated()) {
            $basket = $cart->getBasket();
            $basket->CustomerId = $args->Id;
            if(!$args->Companies->isEmpty()) {
                $basket->CompanyId = $args->Companies->first()->Id;
            }
            StormClient::self()->shopping()->UpdateBasket($basket,1);
        }
        return $args;
    }

}