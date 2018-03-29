<?php

namespace Storm;


use GuzzleHttp\Client;
use League\Container\Container;
use League\Container\ReflectionContainer;
use Noodlehaus\Config;
use Storm\Cache\RedisCache;
use Storm\Http\Batcher;
use Storm\Middleware\ContextParameterMiddleware;
use Storm\Middleware\MiddlewareContainer;
use Storm\Model\StormItem;
use Storm\Model\Support\Cart;
use Storm\Model\Support\CollectionsMapper;
use Storm\Model\Support\EntitiesMapper;
use Storm\Model\Support\OperationsMapper;
use Storm\Model\Support\ParametricsRepository;
use Storm\Model\Support\ProxyRepository;
use Storm\Proxy\AccessClient;
use Storm\Proxy\ApplicationsProxy;
use Storm\Proxy\CustomersProxy;
use Storm\Proxy\ExposeProxy;
use Storm\Proxy\OrdersProxy;
use Storm\Proxy\ProductsProxy;
use Storm\Proxy\ShoppingProxy;
use Storm\Security\Encrypt;
use Storm\Security\RateLimit;
use Storm\Service\BatchServiceProvider;
use Storm\Service\CacheServiceProvider;
use Storm\Service\ConfigurationServiceProvider;
use Storm\Service\CustomerServiceProvider;
use Storm\Service\MiddlewareServiceProvider;
use Storm\Service\SchemeServiceProvider;
use Storm\Service\SecurityServiceProvider;
use Storm\Service\GuzzleServiceProvider;
use Storm\Service\ProxyServiceProvider;
use Storm\Task\Scheme;
use Storm\Util\ServiceCacheMap;
use Storm\Util\StormContext;
use Storm\Util\Str;
use StormWordpress\Admin\Pages;

/**
 * Class StormClient
 * @package Storm
 */
class StormClient
{
    /**
     * @var
     */
    protected $container;
    /**
     * @var array
     */
    protected $configuration = [];

    /**
     * @var array
     */
    protected $configurationDefaults = [
        'application_name' => 'storm_client',
        'certification_path' => '',
        'certification_password' => '',
        'redis_path' => '',
        'app_key' => '',
        'app_path' => '',
        'base_url' => '',
        'image_url' => '',
        'expose_path' => '',
        'middlewares' => [
            'parameters' => [
                //ContextParameterMiddleware::class
            ]
        ]
    ];
    /**
     * @var StormClient
     */
    protected static $instance;
    /**
     * @var bool
     */
    protected $booted = false;
    /**
     * @var
     */
    protected $configurationCallback;
    /**
     * @var
     */
    protected $context;
    /**
     * @var array
     * Array with providers for DI container
     */
    protected $providers = [
        ProxyServiceProvider::class,
        GuzzleServiceProvider::class,
        SecurityServiceProvider::class,
        CacheServiceProvider::class,
        ConfigurationServiceProvider::class,
        CustomerServiceProvider::class,
        BatchServiceProvider::class,
        MiddlewareServiceProvider::class,
        SchemeServiceProvider::class
    ];

    /**
     * StormClient constructor.
     * @param array $configuration
     */
    public function __construct(array $configuration = [])
    {
        $this->configuration = $configuration;
        $this->configuration['app_key'] = file_get_contents($configuration['app_path']);

    }


    /**
     * @param array $configuration
     * @return StormClient
     */
    public static function self($configuration = [])
    {
        if (self::$instance == null) {
            self::$instance = new StormClient($configuration);
            if (!self::$instance->booted) {
                self::$instance->boot();
            }
        } else {
            self::$instance->updateConfig($configuration);
        }

        return self::$instance;
    }

    /**
     * @param $configuration
     */
    public function updateConfig($configuration)
    {
        if (!empty($configuration)) {
            $this->configuration = empty($this->configuration) ? $configuration : array_merge($this->configuration, $configuration);
        }
    }

    /**
     *
     */
    public function boot()
    {
        $this->booted = true;
        /*
         * Builds the container model for the application
         */
        $this->make();
        $this->context = new StormContext();
        $this->refreshServices();
        $this->bootMiddlewares();
    }

    /**
     *
     */
    public function bootMiddlewares()
    {
        foreach ($this->get('middlewares', []) as $key => $middlewares) {
            foreach ($middlewares as $middleware) {
                $this->middlewareContainer()->add($key, new $middleware);
            }
        }
    }

    /**
     * @return mixed|Encrypt
     */
    public function encrypt()
    {
        return $this->container->get('Encrypt');
    }

    /**
     * @param $key
     * @param string $default
     * @return mixed|string
     */
    public function get($key, $default = "")
    {
        $value = $default;
        if ($this->has($key)) {
            $value = $this->configuration[$key];
        }
        return $value;
    }

    /**
     * @param $key
     * @return bool
     */
    public function has($key)
    {
        return isset($this->configuration[$key]);
    }

    /**
     * This removes temporary cachedata used by the batcher
     */
    public function refreshServices()
    {
        $services = ServiceCacheMap::servicesToBeCleared();
        if (empty($services)) {
            return;
        }
        foreach ($services as $service) {
            $this->cache()->multiRemove(ServiceCacheMap::cacheKey($service));
        }
        ServiceCacheMap::resetCleared();
    }

    /**
     * @return MiddlewareContainer
     */
    public function middlewareContainer()
    {
        return $this->container->get('MiddlewareContainer');
    }

    /**
     *
     */
    public function make()
    {
        $this->container = new Container();
        $this->container->delegate(new ReflectionContainer());
        foreach ($this->providers as $provider) {
            $this->container->addServiceProvider(new $provider);
        }

    }

    /**
     * @param callable $configuration
     */
    public function configure(callable $configuration)
    {
        $this->configurationCallback = $configuration;
    }

    /**
     * @return \Closure
     */
    public function getConfigurationCallback()
    {
        if ($this->configurationCallback == null) {
            return function () {
                return [];
            };
        }
        return $this->configurationCallback;
    }

    /**
     * @return Container
     */
    public function container()
    {
        return $this->container;
    }

    /**
     * @return mixed|Batcher
     */
    public function batcher()
    {
        return $this->container->get('Batcher');
    }

    /**
     * @return mixed|ApplicationsProxy
     */
    public function application()
    {
        return $this->container->get('ApplicationsProxy');
    }

    /**
     * @return mixed|ProductsProxy
     */
    public function products()
    {
        return $this->container->get('ProductsProxy');
    }

    /**
     * @return mixed|ExposeProxy
     */
    public function expose()
    {
        return $this->container->get('ExposeProxy');
    }

    /**
     * @return mixed|ShoppingProxy
     */
    public function shopping()
    {
        return $this->container->get('ShoppingProxy');
    }

    /**
     * @return mixed|CustomersProxy
     */
    public function customers()
    {
        return $this->container->get('CustomersProxy');
    }

    /**
     * @return mixed|OrdersProxy
     */
    public function orders()
    {
        return $this->container->get('OrdersProxy');
    }

    /**
     * @return mixed|StormContext
     */
    public function context()
    {
        return $this->context;
    }

    /**
     * @return mixed|OperationsMapper
     */
    public function operationsMapper()
    {
        return $this->container->get('OperationsMapper');
    }

    /**
     * @return mixed|EntitiesMapper
     */
    public function entitiesMapper()
    {
        return $this->container->get('EntitiesMapper');
    }

    /**
     * @return mixed|CollectionsMapper
     */
    public function collectionsMapper()
    {
        return $this->container->get('CollectionsMapper');
    }

    /**
     * @return mixed|RedisCache
     */
    public function cache()
    {
        return $this->container->get('RedisCache');
    }

    /**
     * @return mixed|Config
     */
    public function configuration()
    {
        return $this->container->get('Config');
    }

    /**
     * @return mixed|ParametricsRepository
     */
    public function parametrics()
    {
        return $this->container->get('ParametricsRepository');
    }

    /**
     * @param $onFail
     * @param int $tries
     * @param int $wait
     */
    public function throttle($onFail, $tries = 10, $wait = 30)
    {
        $this->rateLimiter()->throttle($onFail, $tries, $wait);
    }

    /**
     * @return RateLimit
     */
    public function rateLimiter()
    {
        return $this->container->get('RateLimit');
    }

    /**
     * @return mixed|AccessClient
     */
    public function accessClient()
    {
        return $this->container->get('AccessClient');
    }

    /**
     * @return mixed|string
     */
    public function imageBaseUrl()
    {
        return $this->get('image_url', 'https://servicesstage.enferno.se/image/');
    }

    /**
     * @return mixed|Scheme
     */
    public function scheme()
    {
        return $this->container->get('Scheme');
    }

    /**
     * @return mixed|ProxyRepository
     */
    public function proxies()
    {
        return $this->container->get('ProxyRepository');
    }

    /**
     * @return string
     */
    public function exposePath()
    {
        return Str::trailingSlashIt($this->get('expose_path'));
    }

    /**
     * @return \Storm\Model\Support\Cart
     */
    public function cart()
    {
        return $this->container->get('Cart');
    }
}