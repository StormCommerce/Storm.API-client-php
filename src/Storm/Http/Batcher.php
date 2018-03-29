<?php


namespace Storm\Http;


use GuzzleHttp\Middleware;
use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Psr7\Response;
use Storm\Proxy\AccessClient;
use Storm\Util\Str;

/**
 * Class Batcher
 * @package Storm\Http
 */
class Batcher
{
    /**
     * @var array
     */
    protected $queue = [];
    /**
     * @var bool
     */
    protected $resolved = false;
    /**
     * @var bool
     */
    protected $fired = false;
    /**
     * @var AccessClient
     */
    protected $accessClient;

    /**
     * @var Promise
     */
    protected $promise;
    /**
     * @var
     */
    protected $response;

    /**
     * Batcher constructor.
     * @param AccessClient $accessClient
     */
    public function __construct(AccessClient $accessClient)
    {
        $this->accessClient = $accessClient;
    }

    /**
     * @param BatchRequest $request
     */
    public function add(BatchRequest $request)
    {
        $this->queue[$request->__type() . "?" . $request->getCacheKey()] = $request;
    }

    /**
     *
     */
    public function fire()
    {
        $requestAttributes = [];
        $isCached = true;
        foreach ($this->queue as $request) {
            /**
             * @var $request BatchRequest
             */
            $requestAttributes[] = $request->batchParams();
            if (!$request->isCached()) { // only send request if cache is miss
                $isCached = false;
            }
        }
        if (!$isCached) {
            $this->fired = true;
            $this->promise = $this->accessClient->http()->postAsync($this->uri(), ['json' => $requestAttributes]);
        }
    }

    /**
     *
     */
    public function fireIfNotFired()
    {
        if ($this->fired == false) {
            $this->fire();
        }
    }

    /**
     * @param $method
     * @return bool
     */
    public function hasRequest($method)
    {
        foreach ($this->queue as $request) {
            if ($request->method() == $method) {
                $this->resolve();
                return true;
            }

        }
        return false;
    }

    /**
     * This will wait for the requests to finish and resolve them
     * @param $request
     * @throws
     * @throws \Exception|\Throwable
     */
    public function resolve()
    {
        if ($this->promise != null && !$this->resolved) {
            $results = $this->promise->wait(true);
            $this->resolveResponse($results);
            $this->resolved = true;
        }
    }

    /**
     * @param $response Response
     */
    public function resolveResponse($response)
    {
        $this->response = json_decode($response->getBody()->getContents(), true);
        $i = 0;
        foreach ($this->queue as $request) {
            if(isset($this->response[$i])) {
                $request->resolve($this->response[$i]['Result']);
            }
            $i++;
        }
    }

    /**
     * @return string
     */
    public function uri()
    {
        return $this->accessClient->baseUrl() . "ExposeService.svc/rest/Process?format=json";
    }
}