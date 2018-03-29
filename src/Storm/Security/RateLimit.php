<?php


namespace Storm\Security;


use Storm\StormClient;

class RateLimit
{
    private $wait;

    public function throttle($fail, $tries = 10, $wait = 30)
    {
        $this->wait = $wait;

        if ($this->getTries() >= $tries) {
            if ($fail == null) {
                $this->fail();
            } elseif
            (is_callable($fail)) {
                call_user_func($fail);
            } elseif (is_array($fail)) {
                http_response_code(429);
                echo json_encode($fail);
                die;
            } elseif (is_string($fail)) {
                http_response_code(429);
                echo $fail;
                die;
            } else {
                return false;
            }
        } else {
            $this->updateTries();
        }
        return true;
    }

    private function fail()
    {
        http_response_code(429);
        die;
    }

    private function updateTries()
    {
        $tries = $this->getTries();
        $this->cache()->put($this->identifier(), ++$tries, $this->wait);
    }

    private function getTries()
    {
        return $this->cache()->get($this->identifier(), 0);
    }

    private function cache()
    {
        return StormClient::self()->cache();
    }

    public function identifier()
    {
        return StormClient::self()->context()->session() . "_" . $_SERVER['REQUEST_URI'];
    }
}