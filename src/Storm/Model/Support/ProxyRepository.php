<?php


namespace Storm\Model\Support;


use Storm\StormClient;
use Storm\Util\Str;

class ProxyRepository
{
    protected $mappers = ["operations" => "", "entities" => "", "collections" => ""];

    protected $services = [
        "CustomersProxy" => [
            "wsdl" => "CustomerService.svc?singleWsdl",
            "service" => "CustomerService.svc",
            "mappingKey" => "Customers"
        ],
        "ApplicationProxy" => [
            "wsdl" => "ApplicationService.svc?singleWsdl",
            "service" => "ApplicationService.svc",
            "mappingKey" => "Applications"
        ],
        "ShoppingProxy" => [
            "wsdl" => "ShoppingService.svc?singleWsdl",
            "service" => "ShoppingService.svc",
            "mappingKey" => "Shopping"
        ],
        "ProductsProxy" => [
            "wsdl" => "ProductService.svc?singleWsdl",
            "service" => "ProductService.svc",
            "mappingKey" => "Products"
        ],
        "OrdersProxy" => [
            "wsdl" => "OrderService.svc?singleWsdl",
            "service" => "OrderService.svc",
            "mappingKey" => "Orders"
        ],
        "ExposeProxy" => [
            "wsdl" => "ExposeService.svc?singleWsdl",
            "service" => "ExposeService.svc",
            "mappingKey" => "Expose (Empty)",

        ]
    ];
    public function findResolver($entityName) {
        $resolver = null;
        foreach($this->services() as $service ) {
            if(!empty($this->entities($service['mappingKey'])->properties($entityName))) {
                return (new Resolver())->service($service['mappingKey']);
            }
        }
    }
    /**
     * Get service based on Namespace name or proxy name
     * @param $service
     * @return mixed
     */
    public function properties($service)
    {
        if (isset($this->services[$service])) {
            return $this->services[$service];
        }
        foreach ($this->services as $key => $value) { // Search for service based on namespace
            if (Str::contains($service,$key)) {
                return $this->services[$key];
            } elseif(Str::contains($service,$value['mappingKey'])) {
                return $this->services[$key];
            }
        }
    }
    public function services() {
        return $this->services;
    }
    public function operations($service)
    {
        $key = $this->mappingKey($service);
        if(isset($this->mappers['operations'][$key])) {
            return $this->mappers['operations'][$key];
        } else {
            $this->mappers['operations'][$key] = StormClient::self()->operationsMapper()->service($key);
            return $this->mappers['operations'][$key];
        }
    }

    /**
     * @param $service
     * @return mixed|EntitiesMapper
     */
    public function entities($service)
    {
        $key = $this->mappingKey($service);
        if(isset($this->mappers['entities'][$key])) {
            return $this->mappers['entities'][$key];
        } else {
            $this->mappers['entities'][$key] = StormClient::self()->entitiesMapper()->service($key);
            return $this->mappers['entities'][$key];
        }
    }

    public function collections($service)
    {
        $key = $this->mappingKey($service);
        if(isset($this->mappers['collections'][$key])) {
            return $this->mappers['collections'][$key];
        } else {
            $this->mappers['collections'][$key] = StormClient::self()->collectionsMapper()->service($key);
            return $this->mappers['collections'][$key];
        }
    }

    public function mappingKey($service)
    {
        return $this->properties($service)['mappingKey'];
    }

    public function serviceUri($service, $path = "")
    {
        return StormClient::self()->accessClient()->baseUrl() . self::properties($service)['service'] . "/rest/$path";
    }
}