<?php


namespace Storm\Model\Support;


use Storm\Model\StormItem;
use Storm\Proxy\ExposeProxy;
use Storm\StormClient;
use Storm\Util\Arr;
use Storm\Util\Str;

class Resolver
{
    /**
     * @var OperationsMapper[]
     */
    protected $operationsMappers;
    /**
     * @var CollectionsMapper[]
     */
    protected $collectionsMappers;

    /**
     * @var EntitiesMapper[]
     */
    protected $entitiesMappers;

    protected $name;



    public function resolve($json, $returns)
    {
        $returns = $this->evaluateCrossNamespace($returns);
        if (!is_array($json)) {
            $json = json_decode($json, true);
        }
        if ($this->isCollection($returns)) {
            return $this->buildCollection($json, $returns);
        } else {
            $returns = is_array($returns) ? $returns[1] : $returns;
            if (!class_exists($class = "Storm\\Model\\$returns")) {
                $class = StormItem::class;
                $model = new $class($json, $returns);
            } else {
                    $model = new $class($json);
            }

            return $model;
        }
    }

    public function entity($ent)
    {
        $ent = $this->evaluateCrossNamespace($ent);
        if (is_array($ent)) {
            return $this->entitiesMapper($ent[0])->entity($ent[1]);
        } else {
            return $this->entitiesMapper()->entity($ent);
        }
    }

    public function isCollection($class)
    {
        if (is_array($class)) {
            return $this->collectionsMapper($class[0])->isCollection($class[1]);
        } else {
            return $this->collectionsMapper()->isCollection($class);
        }
    }

    private function evaluateCrossNamespace($returns)
    {

        if (!is_array($returns) && Str::contains("Contracts", $returns)) {
            $returns = Str::searchReplace("Contracts.", "", $returns);
            $returns = Arr::explode('.', $returns);
        }

        return $returns;
    }

    public function buildCollection($json, $returns)
    {
        if (is_array($returns)) {
            if ($this->collectionsMapper($returns[0])->isCollection($returns[1])) {
                return $this->collectionsMapper($returns[0])->buildCollection($json, $returns[1]);
            }
        } else {
            return $this->collectionsMapper()->buildCollection($json, $returns);
        }
    }

    public function properties($returns)
    {
        if (is_array($returns)) {
            return $this->entitiesMapper($returns[0])->properties($returns[1]);
        } else {
            return $this->entitiesMapper()->properties($returns);
        }
    }

    public function service($name)
    {
        $this->name = $name;
        return $this;
    }

    public function operationsMapper($service = "")
    {
        if (empty($service)) {
            $service = $this->name;
        }
        return StormClient::self()->proxies()->operations($service);
    }

    public function collectionsMapper($service = "")
    {
        if (empty($service)) {
            $service = $this->name;
        }
        return StormClient::self()->proxies()->collections($service);
    }

    public function entitiesMapper($service = "")
    {
        if (empty($service)) {
            $service = $this->name;
        }
        
        return StormClient::self()->proxies()->entities($service);
    }
    public function getService() {
        return $this->name;
    }
}