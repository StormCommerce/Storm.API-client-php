<?php
namespace Storm\Security;

class Encrypt
{
    protected $key;


    /**
     * Encrypt constructor.
     * @param $key
     */
    public function __construct($key)
    {
        $this->key = $key;

    }

    public function encrypt($message)
    {
        try {
            $encrypted = \Crypto::Encrypt($message, $this->key);
        } catch (\Exception $e) {
            $encrypted = false;
        }
        return $encrypted;
    }

    public function decrypt($message)
    {
        try {
            $decrypted = \Crypto::Decrypt($message, $this->key);
        } catch (\Exception $e) {
            $decrypted = false;
        }
        return $decrypted;
    }

    public static function generateKey()
    {
        return \Crypto::CreateNewRandomKey();
    }
}