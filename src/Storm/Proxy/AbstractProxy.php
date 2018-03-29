<?php


namespace Storm\Proxy;


use League\Uri\Modifiers\AppendSegment;
use Storm\Cache\RedisCache;
use Storm\Model\Failure;
use Storm\Model\Product;
use Storm\Model\StormItem;
use Storm\Http\Batcher;
use Storm\Http\BatchRequest;
use Storm\Model\Support\Collection;
use Storm\Model\Support\CollectionsMapper;
use Storm\Model\Support\EntitiesMapper;
use Storm\Model\Support\NullModel;
use Storm\Model\Support\OperationsMapper;
use Storm\Http\Request;
use Storm\Model\Support\Resolver;
use Storm\StormClient;
use Storm\Util\Str;

abstract class AbstractProxy
{
    protected $cacheable = true;
    /**
     * @var AccessClient
     */
    protected $accessClient;
    /**
     * @var OperationsMapper
     */
    protected $operationsMapper;
    /**
     * @var CollectionsMapper
     */
    protected $collectionsMapper;

    /**
     * @var EntitiesMapper
     */
    protected $entitiesMapper;
    /**
     * @var string
     */
    protected $serviceName;
    /**
     * @var Resolver
     */
    protected $resolver;

    /**
     * @var Resolver
     */
    protected static $latestResolver;

    /**
     * AbstractProxy constructor.
     * @param AccessClient $accessClient
     */
    public function __construct(AccessClient $accessClient)
    {
        $this->accessClient = $accessClient;
    }

    /**
     * @param $name
     * @param $arguments
     * @return Failure|Collection
     */
    public function __call($name, $arguments)
    {
        $this->resolver();
        if (Str::contains('batch', $name)) { // check
            $this->batcher()->add(new BatchRequest($this->serviceName(), Str::searchReplace("batch", "", $name), $arguments));
        } else {
            $request = new Request($this->serviceName(), $name, $arguments);
            if ($request->isCachedOrBatched() && $request->cacheable($this->cacheable())) { // Check if request should be fired
                if(mb_strlen($this->cache()->get($request->getCacheKey())) == 0) {
                    return $this->call($request);
                } else {
                    $returns = $this->operationsMapper()->returns($name);
                    $response = $this->resolver()->resolve($this->cache()->get($request->getCacheKey()), $returns);
                    return $response;
                }
            }
            return $this->call($request);
        }
    }

    /**
     * @return mixed|Batcher
     */
    public function batcher()
    {
        return StormClient::self()->batcher();
    }

    /**
     * @param $key
     * @return string
     */
    public function get($key)
    {
        return $this->accessClient()->http()->get($this->uri($key))->getBody()->getContents();
    }


    /**
     * @param Request $request
     * @return Failure|Collection
     */
    public function call(Request $request)
    {
        $response = $request->execute();

        if ($response->getStatusCode() == 200) {
            $json = $response->getBody()->getContents();
            if (empty($json)) {
                return new NullModel();
            } else {
                $this->cacheResponse($request, $json);
                return $this->resolver()->resolve($json, $this->operationsMapper()->returns($request->method()));
            }
        } else {
            return new Failure([
                'endpoint' => $request->method(),
                'params' => $request->params(),
                'status' => $response->getStatusCode(),
                'body' => $response->getBody()->getContents()
            ]);
        }
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
     * @return AccessClient
     */
    protected function accessClient()
    {
        return $this->accessClient;
    }

    /**
     * @return OperationsMapper
     */
    protected function operationsMapper()
    {
        return StormClient::self()->proxies()->operations($this->serviceName());
    }

    /**
     * @return CollectionsMapper|\Storm\Model\Support\Mapper
     */
    protected function collectionsMapper()
    {
        return StormClient::self()->proxies()->collections($this->serviceName());
    }

    /**
     * @return EntitiesMapper|\Storm\Model\Support\Mapper
     */
    protected function entitiesMapper()
    {
        return StormClient::self()->proxies()->entities($this->serviceName());
    }

    /**
     * @param string $path
     * @return string
     */
    protected function uri($path = "")
    {
        return $this->accessClient()->baseUrl() . $this->serviceName() . "Service.svc/rest/$path";
    }

    /**
     * @return string
     */
    protected function name()
    {
        return (new \ReflectionClass($this))->getShortName();
    }

    /**
     * @return mixed|string
     */
    protected function serviceName()
    {
        if ($this->serviceName == null) {
            $reflection = new \ReflectionClass($this);
            $this->serviceName = str_replace('sProxy', '', $reflection->getShortName());
            $this->serviceName = str_replace('Proxy', '', $reflection->getShortName());
        }
        return $this->serviceName;
    }

    /**
     * @return string
     */
    protected function pluralServiceName()
    {
        return "{$this->serviceName()}s";
    }

    /**
     * @return mixed|Resolver
     */
    protected function resolver()
    {
        if ($this->resolver == null) {
            $this->resolver = (new Resolver())->service($this->serviceName());
            static::$latestResolver = $this->resolver;
        }
        return $this->resolver;
    }

    /**
     * @param Request $request
     * @param $json
     */
    protected function cacheResponse(Request $request, $json)
    {
        if ($this->cacheable()) {
            $this->cache()->put($request->getCacheKey(), $json);
        }
    }

    /**
     * @param $key
     * @param array $data
     * @return string
     */
    protected function getCacheKey($key, $data = [])
    {
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

    public function clearCache()
    {
        $this->cache()->multiRemove("*{$this->serviceName()}*");
    }

    /**
     * @return mixed
     */
    public static function latestResolver()
    {
        return static::$latestResolver;
    }

    public function cacheable()
    {
        return $this->cacheable;
    }
}