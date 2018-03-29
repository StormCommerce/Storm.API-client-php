<?php


namespace Storm\Middleware;


use Storm\StormClient;

class ContextParameterMiddleware extends AbstractMiddleware
{
    public function handle($args)
    {
        return StormClient::self()->context()->injectArgs($args);
    }
}