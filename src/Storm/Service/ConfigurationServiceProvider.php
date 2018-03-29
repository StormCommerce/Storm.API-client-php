<?php


namespace Storm\Service;


use Noodlehaus\Config;
use Storm\StormClient;

class ConfigurationServiceProvider extends StormServiceProvider
{
    public function register()
    {
        $this->add('Config',Config::class)->withArgument($this->requestConfiguration());
    }

    private function requestConfiguration() {
        if(StormClient::self()->cache()->has('client-configuration')) {
            return StormClient::self()->cache()->get('client-configuration');
        }
        $configuration = call_user_func(StormClient::self()->getConfigurationCallback());
        if($configuration == false) {
            return [];
        }
        StormClient::self()->cache()->put('client-configuration',$configuration);
        return $configuration;
    }
}