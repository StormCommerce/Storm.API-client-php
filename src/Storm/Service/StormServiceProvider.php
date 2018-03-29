<?php


namespace Storm\Service;


use League\Container\ServiceProvider\AbstractServiceProvider;

abstract class StormServiceProvider extends AbstractServiceProvider
{
    /**
     * Add an item to the container.
     *
     * @param  string $alias
     * @param  mixed|null $concrete
     * @param  boolean $share
     * @return \League\Container\Definition\DefinitionInterface
     */
    protected function add($alias, $concrete = null, $share = false)
    {
        $this->provides[] = $alias;
        return $this->getContainer()->add($alias, $concrete, $share);
    }
}