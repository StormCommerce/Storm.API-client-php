<?php


namespace Storm\Model\Support;


class EntitiesMapper extends Mapper implements MapperInterface
{
    protected $key = "entities";

    public function build(array $attributes)
    {
        $configuration = [];
        $attributes = isset($attributes['Entities']) ? $attributes['Entities'] : [];
        foreach ($attributes as $ent) {
            $configuration[$ent['Id']] = [];
            $configuration[$ent['Id']]['Properties'] = [];
            foreach ($ent['Properties'] as $property) {
                $configuration[$ent['Id']]['Properties'][$property['Name']] = $property['Type'];
            }
        }
        $this->configuration = $configuration;
        return $this;
    }

    public function properties($ent)
    {
        return isset($this->entity($ent)['Properties']) ? $this->entity($ent)['Properties'] : [];
    }

    public function entity($ent)
    {
        return $this->item($ent);
    }

    public function item($key, $default = [])
    {
        return isset($this->configuration[$key]) ? $this->configuration[$key] : $default;
    }

}