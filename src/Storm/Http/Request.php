<?php


namespace Storm\Http;


use GuzzleHttp\Psr7\Response;
use Storm\Cache\RedisCache;
use Storm\Model\Support\CollectionsMapper;
use Storm\Model\Support\EntitiesMapper;
use Storm\Model\Support\OperationsMapper;
use Storm\Model\Support\Resolver;
use Storm\Model\Support\StormModel;
use Storm\StormClient;
use Storm\Util\ServiceCacheMap;

/**
 * Class Request
 * @package Storm\Model\Support
 */
class Request
{
    /**
     * @var
     */
    protected $service;
    /**
     * @var
     */
    protected $method;
    /**
     * @var array
     */
    protected $args;
    /**
     * @var string
     */
    protected $uri;
    /**
     * @var
     */
    protected $data;
    /**
     * @var EntitiesMapper
     */
    protected $entitiesMapper;
    /**
     * @var OperationsMapper
     */
    protected $operationsMapper;
    /**
     * @var CollectionsMapper
     */
    protected $collectionsMapper;
    /**
     * @var Resolver
     */
    protected $resolver;
    /**
     * @var array
     */
    protected $params;
    /**
     * @var mixed|StormModel
     */
    protected $postParam;
    /**
     * @var mixed
     */
    protected $postParamKey;
    protected $cacheTime = 60;
    protected $noCache = false;
    /**
     * Request constructor.
     * @param $service
     * @param $method
     * @param $args
     */
    public function __construct($service, $method, $args = [])
    {
        $this->service = $service;
        $this->method = $method;
        $this->args = isset($args[0]) && is_array($args[0]) ? $args[0] : $args;
        if (isset($this->args['cacheTime']) && is_numeric($this->args['cacheTime'])) {
            $this->setCacheTime($this->args['cacheTime']);
        }
        if(isset($this->args['noCache']) && $this->args['noCache']) {
            $this->noCache = true;
            $this->setCacheTime(0);
        } 
    }
    public function noCache() {
        return $this->noCache;
    }
    /**
     * @return bool
     */
    public function isBatched()
    {
        return StormClient::self()->batcher()->hasRequest($this->method());
    }

    /**
     * @return mixed|Response
     */
    public function execute()
    {
        $httpVerb = $this->operationsMapper()->method($this->method); // GET or POST
        $options = [];
        $params = $this->params();
        if ($httpVerb == "POST") {
            $this->maybeClearCache();
            if (!empty($this->postParamKey())) {
                $options['json'] = $this->postParam()->postData();
                //$json = json_encode($options['json']);
                unset($params[$this->postParamKey()]);
            }
        }
        $options['query'] = $params;

        return $this->accessClient()->http()->request($httpVerb, $this->uri($this->method), $options);
    }

    public function maybeClearCache()
    {
        if (!ServiceCacheMap::cacheable($this->service())) {
            ServiceCacheMap::clearService($this->service());
        }
    }

    /**
     * @return bool
     */
    public function isCachedOrBatched()
    {
        return $this->isBatched() || $this->isCached();
    }

    /**
     * @return mixed
     */
    public function postParam()
    {
        return $this->params()[$this->postParamKey()];
    }

    /**
     * @return int|string
     */
    public function postParamKey()
    {
        foreach ($this->params() as $key => $value) {
            if ($value instanceof StormModel) {
                return $key;
            }
        }
    }

    /**
     * @return array
     */
    public function params()
    {
        if ($this->params == null) {
            $this->params = $this->operationsMapper()->params($this->method, $this->args);
            $this->params = StormClient::self()->middlewareContainer()->resolve('parameters', $this->params);
            $this->params['format'] = "json";
        }
        return $this->params;
    }

    /**
     * @return OperationsMapper
     */
    protected function operationsMapper()
    {
        return StormClient::self()->proxies()->operations($this->service());
    }

    /**
     * @return mixed
     */
    public function method()
    {
        return $this->method;
    }

    public function cacheable($serviceCacheable = true)
    {
        if ($this->isBatched()) {
            return true;
        } else {
            return $serviceCacheable;
        }
    }

    public function cacheTime()
    {
        return $this->cacheTime;
    }

    public function setCacheTime($time)
    {
        $this->cacheTime = $time;
    }

    /**
     * @return CollectionsMapper|\Storm\Model\Support\Mapper
     */
    protected function collectionsMapper()
    {
        return StormClient::self()->proxies()->collections($this->service());
    }

    /**
     * @return EntitiesMapper|\Storm\Model\Support\Mapper
     */
    protected function entitiesMapper()
    {
        return StormClient::self()->proxies()->entities($this->service());
    }

    /**
     * @param string $path
     * @return string
     */
    protected function uri($path = "")
    {
        return StormClient::self()->proxies()->serviceUri($this->service(), $path);
    }

    /**
     * @return mixed|object|RedisCache
     */
    protected function cache()
    {
        return $this->container()->get('RedisCache');
    }

    /**
     * @return \League\Container\Container
     */
    protected function container()
    {
        return StormClient::self()->container();
    }

    /**
     * @return mixed|object
     */
    protected function accessClient()
    {
        return $this->container()->get('AccessClient');
    }

    /**
     * @param $key
     * @param $params
     * @return mixed
     */
    public function isCached()
    {
        if($this->noCache()) {
            return false;
        }
        $httpVerb = $this->operationsMapper()->method($this->method); // GET or POST

        if ($httpVerb == "POST") {
            return false;
        }

        return $this->cache()->has($this->getCacheKey());
    }

    /**
     * @param $key
     * @param array $data
     * @return string
     */
    public function getCacheKey()
    {
        $key = $this->service() . "-";
        if (!ServiceCacheMap::cacheable($this->service())) {
            $key .= StormClient::self()->context()->session() . "-";
        }
        $key .= $this->method() . "-";
        $data = $this->params();
        foreach ($data as $arrayKey => $value) {
            if (is_array($value)) {
                $data[$arrayKey] = "";
            }
        }
        $key .= "?";
        natsort($data);
        foreach ($data as $arrayKey => $value) {
            $key .= "$arrayKey=$value&";
        }
        return $key;
    }

    public function service()
    {
        return $this->resolver()->getService();
    }

    /**
     * @return Resolver
     */
    protected function resolver()
    {
        if ($this->resolver == null) {
            $this->resolver = new Resolver();
            $this->resolver->service($this->service);
        }
        return $this->resolver;
    }

}