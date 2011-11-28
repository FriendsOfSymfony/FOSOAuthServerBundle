<?php

namespace Alb\OAuth2ServerBundle\Model;

use Alb\OAuth2ServerBundle\Model\OAuth2TokenInterface;

interface OAuth2TokenManagerInterface
{
    function createToken();

    function getClass();

    function findTokenBy(array $criteria);

    function findTokenByToken($token);

    function updateToken(OAuth2TokenInterface $client, $andFlush = true);
}

