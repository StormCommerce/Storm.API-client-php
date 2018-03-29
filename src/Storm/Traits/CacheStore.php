<?php


namespace Storm\Traits;


use Storm\StormClient;

trait CacheStore
{
    public function cache() {
        return StormClient::self()->cache();
    }
}