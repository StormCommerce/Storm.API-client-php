<?php


namespace Storm\Model;


use Storm\Model\Support\Collection;

class EmptyBasket extends Basket
{
    public function __construct(array $attributes = [])
    {
        $attributes['Items'] = new Collection([]);
        parent::__construct($attributes);
    }


    public function isEmpty()
    {
        return true;
    }

    public function getName()
    {
        return "Basket";
    }

}