<?php

namespace FOS\OAuthServerBundle\Propel;

use FOS\OAuthServerBundle\Model\TokenInterface;
use FOS\OAuthServerBundle\Propel\om\BaseToken;

abstract class Token extends BaseToken implements TokenInterface
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
