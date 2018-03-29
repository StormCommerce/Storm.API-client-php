<?php


namespace Storm\Model\Support;


use Storm\Cache\RedisCache;
use Storm\Util\Arr;
use Storm\Util\Str;

/**
 * Class Mapper
 * Helper class to map proxy values correctly
 * @package Storm\Model\Support
 */
class OperationsMapper extends Mapper implements MapperInterface
{

    protected $key = "operations";

    public function build(array $attributes)
    {
        $configuration = [];
        foreach ($attributes['Operations'] as $operation) {
            $configuration[$operation['Id']] = [];
            $configuration[$operation['Id']]['method'] = strtoupper(str_replace('Web', '', $operation['WebAttribute']));
            $configuration[$operation['Id']]['returns'] = $operation['Returns'];
            $params = [];
            foreach ($operation['Parameters'] as $parameter) {
                $type = "string";
                if (isset($parameter['Type'])) {
                    $type = $parameter['Type'];
                }
                $params[$parameter['Name']] = $type;
            }
            $configuration[$operation['Id']]['params'] = $params;
        }
        $this->configuration = $configuration;
        return $this;
    }

    public function method($key)
    {
        return $this->get($key, 'method', "POST");
    }

    public function returns($key)
    {
        return $this->get($key, 'returns', "Storm\\Model\\StormItem");
    }

    public function params($key, $args = [])
    {
        $params = Arr::emptyValues($this->get($key, 'params', []));
        if (Arr::isAssoc($args)) {
            $params = array_merge($params, $args);
        } else {
            $i = 0;
            foreach ($params as $key => $value) {
                if (isset($args[$i])) {
                    $params[$key] = $args[$i];
                } else {
                    $params[$key] = "";
                }
                $i++;
            }
        }

        return $this->filterParams($params);
    }

    public function filterParams($params)
    {
        foreach ($params as $key => $param) {
            if (is_array($param)) {
                $params[$key] = implode(',', $param);
            }
        }
        return $params;
    }
}