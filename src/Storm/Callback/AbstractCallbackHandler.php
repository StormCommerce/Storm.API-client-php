<?php

namespace Storm\Callback;

use Storm\Model\Callback;
use Storm\Model\NameValues;
use Storm\Model\StormItem;
use Storm\Model\Support\PaymentFailure;
use Storm\StormClient;
use Storm\Util\StormContext;

abstract class AbstractCallbackHandler
{

    protected $callback;
    protected $response;
    protected $fail;
    protected $success;

    public function __construct(callable $fail, callable $success)
    {
        $this->fail = $fail;
        $this->success = $success;
        $this->response();
    }

    public function response()
    {
        if ($this->response == null) {
            $this->response = StormClient::self()->shopping()->PaymentCallback2(new NameValues($_REQUEST));
        }
        return $this->response;
    }

    public function evaluate()
    {
        if ($this->isSuccessful()) {
            $this->success();
        } else {
            $this->fail();
        }
    }

    public function isSuccessful()
    {
        return $this->response->Status == "OK";
    }

    public function fail()
    {
        call_user_func($this->fail, new PaymentFailure($this->response));
    }

    public function success()
    {
        StormClient::self()->context()->confirmBasket(); // Move basket to confirmed basket on success
        call_user_func($this->success, $this->response);
    }
}