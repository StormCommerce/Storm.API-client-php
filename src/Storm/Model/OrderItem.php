<?php


namespace Storm\Model;


use Storm\Model\Support\StormModel;
use Storm\Traits\TranslateStatus;
use Storm\Traits\Vatable;

/**
 * Class OrderItem
 * @package Storm\Model
 * @property string PricePerUnit
 */
class OrderItem extends StormModel
{
    use Vatable, TranslateStatus;
    protected $appends = [
        'PricePerUnit'
    ];

    public function getPricePerUnit()
    {
        return $this->RowAmount / $this->QtyOrdered;
    }
    
}