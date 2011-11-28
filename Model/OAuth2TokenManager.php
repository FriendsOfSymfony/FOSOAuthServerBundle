<?php

namespace Alb\OAuth2ServerBundle\Model;

use Doctrine\ORM\EntityManager;

abstract class OAuth2TokenManager implements OAuth2TokenManagerInterface
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

