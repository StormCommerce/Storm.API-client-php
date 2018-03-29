<?php


namespace Storm\Model\Support;


use Storm\Model\StormItem;
use Storm\StormClient;
use Storm\Util\Arr;

class CollectionsMapper extends Mapper implements MapperInterface
{
    protected $key = "collections";

    public function build(array $attributes)
    {
        $configuration = [];
        $attributes = isset($attributes['Collections']) ? $attributes['Collections'] : [];
        foreach ($attributes as $collection) {
            $configuration[$collection['Id']] = [];
            $configuration[$collection['Id']]['ItemName'] = $collection['ItemName'];
            $configuration[$collection['Id']]['ItemType'] = $collection['ItemType'];
        }
        $this->configuration = $configuration;
        return $this;
    }

    public function isCollection($key)
    {
        return key_exists($key, $this->configuration);
    }

    public function buildCollection(array $response, $type)
    {
        $type = $this->get($type, 'ItemType', "");
        $setName = false;
        if (!class_exists($class = "Storm\\Model\\$type")) {
            $class = StormItem::class;
            $setName = true;
        }
        if(Arr::isAssoc($response)) {
            $items = isset($response['Items']) ? $response['Items'] : [];
        } else {
            $items = $response;
        }
        $collection = new Collection();
        foreach ($items as $item) {
            if($setName) {
                $model = new $class($item,$type);
                $collection->add($model);
            } else {
                $collection->add(new $class($item));
            }
        }

        return $collection;
    }
}