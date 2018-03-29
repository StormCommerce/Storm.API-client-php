<?php


namespace Storm\Proxy;

use League\Uri\Schemes\Http as HttpUri;
use GuzzleHttp\Client;
use League\Uri\Schemes\Http;
use Storm\StormClient;
use Storm\Util\Str;

class AccessClient
{
    /**
     * @var
     */
    protected $http;

    /**
     * AccessClient constructor.
     */
    public function __construct(Client $guzzle)
    {
        $this->http = $guzzle;
    }

    /**
     * Returns underlying http client with signed cert
     * @return Client
     */
    public function http()
    {
        return $this->http;
    }

    /**
     * @return static|Http
     */
    public function baseUrl()
    {
        return Str::trailingSlashIt(StormClient::self()->get('base_url', "https://servicesstage.enferno.se/api/1.1/"));
    }
}