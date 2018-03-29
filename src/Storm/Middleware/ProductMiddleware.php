<?php


namespace Storm\Middleware;


use Storm\Model\Product;

abstract class ProductMiddleware extends AbstractMiddleware
{
    /**
     * @return Product
     */
    public function getProduct()
    {
        return $this->data;
    }

    public function handle($args)
    {
        return parent::handle($args); // TODO: Change the autogenerated stub
    }

}