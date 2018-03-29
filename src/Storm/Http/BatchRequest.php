<?php


namespace Storm\Http;


use Storm\Model\Response;

class BatchRequest extends Request
{
    protected $service;
    protected $method;
    protected $args;
    protected $response;
    protected $params;

    public function method()
    {
        return $this->method;
    }

    public function __type()
    {
        return "{$this->method()}Request:Enferno.Services.Contracts.Expose";
    }


    public function batchParams()
    {
        $params = [
            "__type" => $this->__type()
        ];
        $paramsOriginal = $this->params();
        unset($paramsOriginal['format']);
        foreach ($paramsOriginal as $key => $param) {
            $params[ucfirst($key)] = $param;
        }
        return $params;
    }

    public function resolve($attributes)
    {
        $this->cache()->put($this->getCacheKey(),json_encode($attributes),$this->cacheTime());
    }
}