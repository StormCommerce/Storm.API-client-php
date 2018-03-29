<?php


namespace Storm\Model\Support;


use Storm\StormClient;

class Customer
{
    protected $customer;

    public function customer() {

    }
    public function context() {
        return StormClient::self()->context();
    }
}