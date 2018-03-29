<?php


namespace Storm\Model;


use Storm\StormClient;
use Storm\Util\Arr;

class ProductItem extends Product
{

    public function addToCartButton()
    {

    }

    public function images()
    {
        return [$this->imageUrl()];
    }

    public function getFeaturesList()
    {
        $ids = Arr::explode(',', $this->CategoryIdSeed);
        if (empty($ids)) {
            return [];
        }
        $id = $ids[0];
        $focusParametrics = $this->getFocusParametricsList($id)->lists('Id');
        $list = [];
        $values = Arr::explode(",", $this->ParametricValueSeed);
        foreach ($values as $value) {
            $value = Arr::explode(':', $value);
            if (!in_array($value[0], $focusParametrics)) {
                continue;
            }
            $info = StormClient::self()->parametrics()->info($value[0]);
            $parametricValue = 0;
            if (isset($value[1])) {
                $parametricValue = $value[1];
            }
            $list[$info->Name] = $parametricValue . " " . $info->Uom;
        }
        $values = $values = Arr::explode(",", $this->ParametricListSeed);
        $values = array_merge($values, Arr::explode(',', $this->ParametricMultipleSeed));
        foreach ($values as $value) {
            $value = Arr::explode(':', $value);
            if (!in_array($value[0], $focusParametrics)) {
                continue;
            }
            $info = StormClient::self()->parametrics()->info($value[0]);
            $parametricValue = 0;
            if (isset($value[1])) {
                $parametricValue = StormClient::self()->parametrics()->values($value[1]);
            }
            if($parametricValue instanceof StormItem) {
                $list[$info->Name] = $parametricValue->Name . " " . $info->Uom;
            } else {
                $list[$info->Name] = $parametricValue . " " . $info->Uom;
            }


        }
        return $list;
    }
}