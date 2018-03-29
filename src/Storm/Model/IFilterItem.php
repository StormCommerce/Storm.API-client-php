<?php


namespace Storm\Model;


use Storm\Model\Support\StormModel;
use Storm\StormClient;

class IFilterItem extends StormModel
{
    public function getType() {
        return StormClient::self()->parametrics()->info($this->Id);
    }
    public function getValues() {
        return StormClient::self()->parametrics()->values($this->Id);
    }
}