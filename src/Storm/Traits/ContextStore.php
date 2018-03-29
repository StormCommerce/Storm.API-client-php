<?php


namespace Storm\Traits;


use Storm\StormClient;

trait ContextStore
{
    public function context()
    {
        return StormClient::self()->context();
    }
}