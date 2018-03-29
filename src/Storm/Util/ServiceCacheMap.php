<?php


namespace Storm\Util;


use Storm\StormClient;

class ServiceCacheMap
{
    const CLEAR_SERVICES_KEY = "storm-clear-services";
    protected static $cacheable = [
        "Products",
        "Product"
    ];

    public static function cacheable($service)
    {
        return in_array($service, static::$cacheable);
    }

    public static function clearService($service)
    {
        $cache = StormClient::self()->cache();
        Arr::explode(",", $cache->get(static::key(), ""));
        $services[] = $service;
        $cache->forever(static::key(), implode(",", $services));
    }

    private static function key()
    {
        return static::CLEAR_SERVICES_KEY . "-" . StormClient::self()->context()->session();
    }

    public static function resetCleared()
    {
        $cache = StormClient::self()->cache();
        $cache->forever(static::key(), "");
    }

    public static function servicesToBeCleared()
    {
        $cache = StormClient::self()->cache();
        return Arr::explode(",", $cache->get(static::key(), ""));
    }

    public static function cacheKey($service)
    {
        return $service . "-" . StormClient::self()->context()->session() . "-*";
    }
}