<?php

namespace Alb\OAuth2ServerBundle\Model;

abstract class OAuth2AuthCodeManager implements OAuth2AuthCodeManagerInterface
{
    public function createAuthCode()
    {
        $class = $this->getClass();
        return new $class;
    }

    public function findAuthCodeByCode($code)
    {
        return $this->findAuthCodeBy(array('code' => $code));
    }
}

