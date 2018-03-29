<?php


namespace Storm\Traits;


trait Vatable
{
    public function vatRate() {
        if(mb_strlen($this->VatRate) > 0) {
            if($this->VatRate >= 2 ) {
                return ($this->VatRate /100) + 1;
            } else {
                return $this->VatRate;
            }
        } else {
            return 1; // No Vatrate
        }
    }
}