<?php


namespace Storm\Util;


class Arr
{
    public static function isAssoc($arr)
    {
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    public static function emptyValues($arr)
    {
        return array_map(create_function('$n', 'return "";'), $arr);
    }

    public static function explode($delimiter, $string)
    {
        $array = explode($delimiter, $string);
        $array = static::filter($array);
        return array_values($array);
    }

    public static function filter($array, $callback = null)
    {
        if ($callback == null) {
            return array_filter($array, function ($item) {
                if ($item == null) {
                    return false;
                }
                return mb_strlen($item) > 0;
            });
        } else {
            return array_filter($array, $callback);
        }
    }

    public static function replace($find, $replace, $array)
    {
        if (!is_array($array)) {
            if (is_string($array)) {
                return Str::searchReplace($find, $replace, $array);
            }
            return $array;
        }
        $newArray = array();
        foreach ($array as $key => $value) {
            $newArray[$key] = Arr::replace($find, $replace, $value);
        }
        return $newArray;
    }
}