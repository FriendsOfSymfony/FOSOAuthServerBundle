<?php

namespace Alb\OAuth2ServerBundle\Model;

abstract class OAuth2AuthCodeManager implements OAuth2AuthCodeManagerInterface
{
    public function createAuthCode()
    {
        $class = $this->getClass();
        return new $class;
    }

    public function findAuthCodeByToken($token)
    {
        return $this->findAuthCodeBy(array('token' => $token));
    }
}

