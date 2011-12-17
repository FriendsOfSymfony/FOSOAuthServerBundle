<?php

namespace FOS\OAuthServerBundle\Model;

use FOS\OAuthServerBundle\Model\TokenInterface;

interface TokenManagerInterface
{
    function createToken();

    function getClass();

    function findTokenBy(array $criteria);

    function findTokenByToken($token);

    function updateToken(TokenInterface $token);

    function deleteToken(TokenInterface $token);
}

