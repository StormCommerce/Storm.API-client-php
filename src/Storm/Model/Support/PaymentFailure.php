<?php


namespace Storm\Model\Support;


use Storm\Model\Failure;
use Storm\Util\Str;

class PaymentFailure
{
    protected $failure;

    /**
     * PaymentFailure constructor.
     * @param Failure $failure
     */
    public function __construct($failure)
    {
        $this->failure = $failure;
    }

    public function status()
    {
        $body = $this->failure->get('body');
        if (Str::contains('CANCELLED', $body)) {
            return 'CANCELLED';
        } else {
            return 'ERROR';
        }
    }
    public function message() {
        return $this->failure->get('body');
    }
}