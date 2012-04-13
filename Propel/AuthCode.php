<?php

namespace FOS\OAuthServerBundle\Propel;

use FOS\OAuthServerBundle\Model\AuthCodeInterface;
use FOS\OAuthServerBundle\Propel\om\BaseAuthCode;

class AuthCode extends BaseAuthCode implements AuthCodeInterface
{
    protected $data;

    public function setData($data)
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getExpiresIn()
    {
        if ($this->getExpiresAt()) {
            return $this->getExpiresAt() - time();
        } else {
            return PHP_INT_MAX;
        }
    }

    public function hasExpired()
    {
        if ($this->getExpiresAt()) {
            return time() > $this->getExpiresAt();
        }

        return false;
    }
}
