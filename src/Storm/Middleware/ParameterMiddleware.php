<?php


namespace Storm\Middleware;


use Storm\Util\Arr;

abstract class ParameterMiddleware extends AbstractMiddleware
{
    public function parameterMerge($args, $key, $value)
    {
        if (!isset($args[$key])) {
            return $args;
        }

        if (empty($args[$key])) {
            if (is_array($value)) {
                $value = implode(",", $value);
            }
            $args[$key] = $value;
        } else {
            if (!is_array($value)) {
                $value = Arr::explode(",", $value);
            }
            $param = Arr::explode(",", $args[$key]);
            $args[$key] = array_merge($param, $value);
        }
        
        return $args;
    }
}