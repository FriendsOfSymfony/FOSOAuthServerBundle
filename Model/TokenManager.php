<?php

namespace FOS\OAuthServerBundle\Model;

abstract class TokenManager implements TokenManagerInterface
{
    public function createToken()
    {
        $class = $this->getClass();
        return new $class;
    }

    public function findTokenByToken($token)
    {
        return $this->findTokenBy(array('token' => $token));
    }
}

