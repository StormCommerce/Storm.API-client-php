<?php


namespace Storm\Service;


use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Handler\CurlHandler;
use League\Container\ServiceProvider\AbstractServiceProvider;
use Storm\StormClient;

class GuzzleServiceProvider extends StormServiceProvider
{

    protected $provides = [
        'Guzzle',
        'CookieJar'
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
        $cert = StormClient::self()->get('certification_path');
        $password = StormClient::self()->get('certification_password');
        $handler = new CurlHandler();

        $this->add('CookieJar', CookieJar::class);
        $this->add('Guzzle', Client::class)->withArgument(['cert' => [$cert, $password], 'cookies' => $this->getContainer()->get('CookieJar'), 'handler' => $handler]);
    }
}