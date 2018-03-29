<?php


namespace Storm\Service;

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
use League\Container\ServiceProvider\AbstractServiceProvider;
use Storm\Proxy\ShoppingProxy;

class ProxyServiceProvider extends StormServiceProvider
{
    protected $provides = [
        'ProxyRepository',
        'AccessClient',
        'ProductsProxy',
        'ExposeProxy',
        'EntitiesMapper',
        'CollectionsMapper',
        'OperationsMapper',
        'ParametricsRepository',
        'CustomersProxy',
        'ShoppingProxy',
        'OrdersProxy',
        'ApplicationsProxy'
    ];

    /**
     * Use the register method to register items with the container via the
     * protected $this->container property or the `getContainer` method
     * from the ContainerAwareTrait.
     *
     * @return void
     */
    public function register()
    {
        $this->add('ProxyRepository', ProxyRepository::class, true);
        $this->add('ParametricsRepository', ParametricsRepository::class, true);
        $this->add('EntitiesMapper', EntitiesMapper::class)->withArgument('RedisCache');
        $this->add('CollectionsMapper', CollectionsMapper::class)->withArgument('RedisCache');
        $this->add('OperationsMapper', OperationsMapper::class)->withArgument('RedisCache');
        $this->add('AccessClient', AccessClient::class)->withArgument('Guzzle');
        $this->add('ProductsProxy', ProductsProxy::class)->withArgument('AccessClient');
        $this->add('ExposeProxy', ExposeProxy::class)->withArgument('AccessClient');
        $this->add('CustomersProxy', CustomersProxy::class)->withArgument('AccessClient');
        $this->add('ShoppingProxy', ShoppingProxy::class)->withArgument('AccessClient');
        $this->add('OrdersProxy', OrdersProxy::class)->withArgument('AccessClient');
        $this->add('ApplicationsProxy', ApplicationsProxy::class)->withArgument('AccessClient');
    }
}