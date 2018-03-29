<?php


namespace Storm\Model;


class NameValues extends StormItem
{
    public function __construct(array $attributes = [])
    {
        $attr = [];
        foreach ($attributes as $key => $value) {
            $attr[] = new StormItem([
                'Name' => $key,
                'Value' => $value
            ], 'NameValue');
        }
        parent::__construct($attr, 'NameValues');
    }

}