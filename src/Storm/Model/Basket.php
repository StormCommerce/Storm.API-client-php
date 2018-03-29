<?php


namespace Storm\Model;


use Storm\Model\Support\Collection;
use Storm\Model\Support\StormModel;

/**
 * Class Basket
 * @package Storm\Model
 */
class Basket extends StormModel
{
    /**
     * @var array
     */
    protected $filtered = [];
    /**
     * @var array
     */
    protected $appends = ["ItemCount"];

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return $this->Items->count() > 0;

    }

    /**
     * @return mixed
     */
    public function getItemCount()
    {
        return $this->filteredItems()->count();
    }

    /**
     * @return array|Collection|BasketItem[]
     */
    public function filteredItems()
    {
        if (empty($this->filtered)) {
            $this->filtered = $this->Items->filter(function ($item) {
                return $item->Type !== Product::FREIGHT && $item->PartNo !== 'invoice';
            });
        }
        return $this->filtered;
    }
}