<?php
namespace Storm\Cache;

use Predis\Client;
use Storm\StormClient;
use Storm\Util\Str;

class RedisCache
{
    /**
     * @var Client
     */
    protected $client;
    protected $prefix = "storm-";

    /**
     * Cache constructor.
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function get($key, $default = "")
    {
        if (!Str::startsWith($this->prefix(), $key)) {
            $key = $this->prefix() . $key;
        }
        $value = $default;
        $cached = $this->client->get($key);
        if ($cached != null) {
            $value = unserialize($cached);
        }
        return $value;
    }

    public function has($key)
    {
        if (!Str::startsWith($this->prefix(), $key)) {
            $key = $this->prefix() . $key;
        }
        return $this->client->exists($key);
    }

    public function put($key, $value, $minutes = 60)
    {
        if (!Str::startsWith($this->prefix(), $key)) {
            $key = $this->prefix() . $key;
        }
        $this->client->setex($key, round($minutes * 60), serialize($value));
    }

    public function forever($key, $value)
    {
        if (!Str::startsWith($this->prefix(), $key)) {
            $key = $this->prefix() . $key;
        }
        return $this->client->set($key, serialize($value));
    }

    public function keys($pattern)
    {
        return $this->client->keys($pattern);
    }

    public function multiRemove($key)
    {
        if (!Str::startsWith('*', $key)) {
            $key = "*$key";
        }
        if (!Str::endsWith('*', $key)) {
            $key = "$key*";
        }
        $keys = $this->keys($key);
        foreach ($keys as $delKey) {
            $this->remove($delKey);
        }
    }

    public function remove($key)
    {
        if (!Str::startsWith($this->prefix(), $key)) {
            $key = $this->prefix() . $key;
        }
        $this->client->del([$key]);
    }

    public function prefix()
    {
        return StormClient::self()->get('application_name') . "_";
    }
}