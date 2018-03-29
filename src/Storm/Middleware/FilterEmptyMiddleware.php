<?php


namespace Storm\Middleware;


class FilterEmptyMiddleware extends AbstractMiddleware
{
    public function handle($args)
    {
        return array_filter($args, function($item) {
            if(is_string($item)) {
                return mb_strlen($item) > 0;
            }
            return true;
        });
    }

}