<?php


namespace Storm\Model\Support;


use Storm\Cache\RedisCache;
use Storm\StormClient;
use Storm\Util\Str;

abstract class Mapper
{
    /**
     * @var array
     */
    protected $configuration;
    /**
     * @var RedisCache
     */
    protected $cache;
    public $service;
    protected $key;


    /**
     * Mapper constructor.
     * @param RedisCache $cache
     */
    public function __construct(RedisCache $cache)
    {
        $this->cache = $cache;
    }
    public function build(array $attributes) {
        $this->configuration = $attributes;
        return $this;
    }
    public function service($name)
    {
        $mapper = new static($this->cache);
        $mapper->service = $name;
        return $mapper->load();
    }
    public function load()
    {
        $this->configuration = $this->cache->get($this->cacheKey());
        if (!empty($this->configuration)) {
            $this->configuration = json_decode($this->configuration,true);
        } else {
            if(file_exists($this->filePath())) {
                $this->configuration = include $this->filePath();
                $this->saveCache();
            } else {
                $this->configuration = [];
            }
        }
        return $this;
    }

    public function get($name, $key, $default)
    {
        $value = $default;
        if (isset($this->configuration[$name][$key])) {
            $value = $this->configuration[$name][$key];
        }
        return $value;
    }

    protected function filePath()
    {
        return StormClient::self()->exposePath() . "{$this->mapKey()}.php";
    }

    protected function cacheKey()
    {
        return $this->mapKey();
    }

    protected function mapKey()
    {
        return "storm-mapping-{$this->service}-{$this->key}";
    }


    public function saveCache()
    {
        return $this->cache->forever($this->cacheKey(), json_encode($this->configuration));
    }

    public function saveFile()
    {
        $data = "<?php " . PHP_EOL;
        $data .= "return " . var_export($this->configuration, true) . ";";
        return file_put_contents($this->filePath(), $data);
    }

    public function save()
    {
        $this->saveCache();
        $this->saveFile();
    }
}