<?php


namespace Storm\Model;


use Storm\Model\Support\StormModel;
use Storm\StormClient;

/**
 * Default model if not defined
 * @package Storm\Model
 */
class StormItem extends StormModel
{
    protected $name;

    /**
     * StormItem constructor.
     * @param $name
     */
    public function __construct($attributes, $name)
    {
        $this->name = $name;
        $this->fill($attributes);
    }

    public function setName($name) {
        $this->name = $name;
    }
    public function getName() {
        return $this->name;
    }

    public function getEnt()
    {
        return StormClient::self()->entitiesMapper()->entity($this->getName());
    }
}