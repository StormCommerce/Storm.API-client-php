<?php


namespace Storm\Traits;


use Storm\StormClient;

trait ProductStore
{
    public function products() {
        return StormClient::self()->products();
    }
}