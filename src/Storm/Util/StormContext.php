<?php

namespace Storm\Util;

use Storm\Model\Application;
use Storm\Model\Customer;
use Storm\StormClient;

/**
 * Class StormContext
 * @package Storm\Util
 */
class StormContext
{
    /**
     * @var Application The storm application
     */
    protected $application;
    /**
     * @var string
     */
    protected $cookieName = "StormPersisted";
    /**
     * @var array
     */
    protected $keys = [
        "LoginName",
        "AccountId",
        "CustomerId",
        "CompanyId",
        "DivisionId",
        "BasketId",
        "ReferId",
        "RememberMe",
        "ConfirmedBasketId",
        "CultureCode",
        "CurrencyId",
        "Currency",
        "PriceListIds",
        "PriceListIdSeed",
        "SalesAreaId",
        "ShowPricesIncVat",
        "IsInitialRequest",
        "Session",
        "IsCompany"
    ];
    protected $merge = [
        'priceListSeed' => 'PriceListIds',
        'customerId' => 'CustomerId',
        'cultureCode' => 'CultureCode',
        'currencyId' => 'CurrencyId'
    ];
    /**
     * @var array
     */
    protected $cookie;

    /**
     * StormContext constructor.
     */
    public function __construct()
    {
        if (!isset($_COOKIE[$this->cookieName])) {
            $this->cookie = $this->buildDefaults();
        }

    }

    public function reset()
    {
        $this->cookie = $this->buildDefaults();
    }

    public function injectArgs($args)
    {
        foreach ($this->merge as $param => $context) {
            if (isset($args[$param])) {
                $args[$param] = $this->mergeArg($args[$param], $this->get($context, ''));
            }
        }
        return $args;
    }

    private function mergeArg($arg, $value)
    {
        if (empty($value)) {
            return $arg;
        }
        if (empty($arg)) {
            $arg = $value;
        } else {
            $arg = Arr::explode(",", $arg);
            if (!in_array($value, $arg)) {
                $arg[] = $value;
            }
            $arg = implode(',', $arg);
        }
        return $arg;
    }

    /**
     * @return array
     */
    private function buildDefaults()
    {
        $cookie = [];
        foreach ($this->keys as $key) {
            $cookie[$key] = "";
        }

        $this->build($cookie);
        return $cookie;
    }

    /**
     * @return \Storm\Model\Culture
     */
    public function culture()
    {
        return $this->application()->Cultures->Default;
    }

    /**
     * @return \Storm\Model\Currency
     */
    public function currency()
    {
        $application = $this->application();
        return $application->Currencies->Default;
    }

    /**
     * @return \Storm\Model\Application
     */
    private function application()
    {
        if ($this->application == null) {
            $this->application = StormClient::self()->application()->GetApplication();
        }
        return $this->application;
    }

    /**
     * @param array $cookie
     */
    private function build(array $cookie = [])
    {
        setcookie($this->cookieName, $this->crypto()->encrypt(json_encode($cookie)), null, "/");
    }

    /**
     * @return mixed|\Storm\Security\Encrypt
     */
    private function crypto()
    {
        return StormClient::self()->encrypt();
    }

    /**
     * @return array
     */
    private function deserialize()
    {
        if ($this->cookie != null) {
            return $this->cookie;
        }
        if (isset($_COOKIE[$this->cookieName])) {
            $this->cookie = json_decode($this->crypto()->decrypt($_COOKIE[$this->cookieName]), true);
        } else {
            $this->cookie = $this->buildDefaults();
        }
        return $this->cookie;
    }

    /**
     * Logins in the user, return Customer if success and false if fail
     * @param $user
     * @param $password
     * @return bool|Customer
     */
    public function login($user, $password)
    {
        $customer = $this->customers()->Login($user, $password);
        if ($customer instanceof Customer) {
            $this->setUser($customer);
            return $customer;
        }
        return false;
    }

    public function updateUser()
    {
        if ($this->isLoggedIn()) {
            $customer = $this->customers()->GetCustomer($this->user()->Id);
            if ($customer instanceof Customer) {
                $this->setUser($customer);
            }
        }
    }

    public function latestPaymentError()
    {
        $message = $this->get('latest-payment-error', '');
        $message = json_decode($message, true);
        if (isset($message['Message'])) {
            return $message['Message'];
        } else {
            return '';
        }
    }

    public function setLatestPaymentError($message)
    {
        $this->set('latest-payment-error', $message);
    }

    public function logout()
    {
        if ($this->cache()->has($this->userKey())) {
            $this->cache()->remove($this->userKey());
            $this->reset();
        }
        return true;
    }

    public function isLoggedIn()
    {
        return $this->cache()->has($this->userKey());
    }

    /**
     * @param $customer
     */
    public function setUser($customer)
    {
        $customer = StormClient::self()->middlewareContainer()->resolve('logged_in_customer', $customer);
        $this->cache()->forever($this->userKey(), $customer->toJson());
    }

    /**
     * @return bool|Customer
     */
    public function user()
    {
        if ($this->cache()->has($this->userKey())) {
            return new Customer(json_decode($this->cache()->get($this->userKey(), ""), true));
        }
        return false;
    }

    private function cache()
    {
        return StormClient::self()->cache();
    }

    private function customers()
    {
        return StormClient::self()->customers();
    }

    /**
     * @param $key
     * @param null $default
     * @return mixed|null
     */
    public function get($key, $default = null)
    {
        if ($this->cookie == null) {
            $this->deserialize();
        }
        $value = $default;
        if ($this->has($key)) {
            $value = $this->cookie[$key];
        }
        if (mb_strlen($value) == 0 && in_array($key, $this->keys) && $this->isLoggedIn()) {
            $user = $this->user();
            switch ($key) {
                case "LoginName":
                    $value = $user->Account->LoginName;
                    break;
                case "AccountId":
                    $value = $user->Account->Id;
                    break;
                case "CustomerId":
                    $value = $user->Id;
                    break;
                case "CompanyId":
                    if(!$user->Companies->isEmpty()) {
                        $value = $user->Companies->first()->Id;
                    }
                    break;
            }
        }
        return $value;
    }

    /**
     * @param $key
     * @param $value
     */
    public function set($key, $value)
    {
        $this->deserialize();
        $this->cookie[$key] = $value;
        $this->build($this->cookie);
    }

    /**
     * @param $key
     * @return bool
     */
    public function has($key)
    {
        $this->deserialize();
        return !empty($this->cookie[$key]);
    }

    /**
     * @return mixed
     */
    public function toString()
    {
        return print_r($this->toArray(), true);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->deserialize();
    }

    /**
     * @return mixed|null
     */
    public function session()
    {
        if (!$this->has('Session')) {
            $session = uniqid('storm', true);
            $this->set('Session', $session);
        } else {
            $session = $this->get('Session');
        }
        return $session;
    }

    private function userKey()
    {
        return $this->session() . "_User";
    }

    /**
     * @return mixed|null
     */
    public function isCompany()
    {
        $isCompany = $this->get('IsCompany', false);
        if (!(is_bool($isCompany))) {
            $isCompany = boolval($isCompany);
        }
        return $isCompany;
    }

    /**
     * @param bool $bool
     */
    public function setIsCompany($bool = false)
    {
        $this->set('IsCompany', $bool);
    }

    /**
     *
     */
    public function confirmBasket()
    {
        $this->set('ConfirmedBasketId', $this->get('BasketId'));
        $this->set('BasketId', '');
    }


}