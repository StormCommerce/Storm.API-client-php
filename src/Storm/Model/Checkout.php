<?php


namespace Storm\Model;


use Storm\Model\Support\StormModel;

/**
 * Class Checkout
 * @package Storm\Model
 */
class Checkout extends StormModel
{
    /**
     * @return null|PaymentMethod
     */
    public function selectedPaymentMethod()
    {
        $method = null;
        foreach ($this->PaymentMethods as $paymentMethod) {
            if ($paymentMethod->IsSelected) {
                $method = $paymentMethod;
            }
        }
        return $method;
    }

    /**
     * @return null|DeliveryMethod
     */
    public function selectedDeliveryMethod()
    {
        $method = null;
        foreach ($this->DeliveryMethods as $deliveryMethod) {
            if ($deliveryMethod->IsSelected) {
                $method = $deliveryMethod;
            }
        }
        return $method;
    }
}