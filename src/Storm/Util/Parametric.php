<?php


namespace Storm\Util;


class Parametric
{

    /**
     * Merge parametrics strings e.g V1232_1-100 <-> V1232_2-500 = V1232_1-500
     *
     * @param $parametrics
     * @param $mergeWith
     */
    public static function merge($parametrics, $mergeWith)
    {
        $values = [];
        $parametricIds = [];
        $parametricList = Arr::explode('*', $parametrics);
        $mergeWithList = Arr::explode('*', $mergeWith);
        $parametricIds = static::buildList($parametricIds, $parametricList);
        $parametricIds = static::buildList($parametricIds, $mergeWithList);
        foreach ($parametricIds as $id => $values) {
            if (Str::contains('V', $id)) {
                $lowestValue = null;
                $highestValue = null;
                foreach ($values as $value) {
                    $exploded = Arr::explode('-', $value);
                    $lowValue = $exploded[0];
                    $highValue = $exploded[1];
                    if ($lowestValue == null) {
                        $lowestValue = $lowValue;
                    } else {
                        if ($lowValue < $lowestValue) {
                            $lowestValue = $lowValue;
                        }
                    }
                    if ($highestValue == null) {
                        $highestValue = $highValue;
                    } else {
                        if ($highValue > $highestValue) {
                            $highestValue = $highValue;
                        }
                    }
                    $values[] = "V{$id}_{$lowestValue}-{$highestValue}";
                }
            }
        }
        return implode('*', array_unique($values));
    }

    private static function buildList($array, $parametricList)
    {
        foreach ($parametricList as $parametric) {
            if (Str::contains('V', $parametric)) {
                $values = Arr::explode($parametric, '_');
                if (count($values) !== 2) {
                    continue;
                }
                if (isset($parametricIds[$values[0]])) {
                    $array[$values[0]][] = $values[1];
                } else {
                    $array[$values[0]] = $values[1];
                }
            }
        }
        return $array;
    }
}