<?php


namespace Storm\Model\Support;


use Storm\Contract\Taskable;
use Storm\StormClient;

/**
 * Class ParametricsRepository
 * @package Storm\Model\Support
 */
class ParametricsRepository implements Taskable
{
    /**
     * @var
     */
    protected $info;
    /**
     * @var
     */
    protected $values;
    //Types
    /**
     *
     */
    const LIST_VALUE = 1;
    /**
     *
     */
    const MULTI_VALUE = 2;
    /**
     *
     */
    const VALUE = 3;
    //Value types
    /**
     *
     */
    const TEXT = 1;
    /**
     *
     */
    const INT = 2;
    /**
     *
     */
    const DECIMAL = 3;
    /**
     *
     */
    const BOOL = 4;
    /**
     *
     */
    const HTML = 5;
    /**
     *
     */
    const DATUM = 6;

    /**
     * @param $id
     * @return null
     */
    public function info($id)
    {
        if ($this->info == null) {
            $this->info = $this->products()->ListParametricInfo(); // Will load from cache if possible
        }
        foreach ($this->info as $parametricInfo) {
            if ($parametricInfo->Id == $id) {
                return $parametricInfo;
            }
        }
        return null;
    }

    /**
     * @param $id
     * @return null
     */
    public function values($id)
    {
        if ($this->values == null) {
            $this->values = $this->products()->ListParametricValues2();
        }
        foreach ($this->values as $value) {
            if ($value->Id == $id) {
                return $value;
            }
        }
        return null;
    }
    
    /**
     * @return mixed|\Storm\Proxy\ProductsProxy
     */
    private function products()
    {
        return StormClient::self()->products();
    }

    /**
     * Caches things that is frequently used
     */
    public static function task()
    {
        $categories = StormClient::self()->products()->ListCategories();
        foreach ($categories as $category) {
            StormClient::self()->products()->batchListFocusParametrics($category->Id);
        }
        StormClient::self()->products()->batchListParametricInfo(['cacheTime' => 1440]);
        StormClient::self()->products()->batchListParametricValues2(['cacheTime' => 1440]);
    }

}