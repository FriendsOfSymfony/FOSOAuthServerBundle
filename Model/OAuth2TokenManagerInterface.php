<?php

namespace FOS\OAuthServerBundle\Model;

use FOS\OAuthServerBundle\Model\OAuth2TokenInterface;

interface OAuth2TokenManagerInterface
{
    function createToken();

    function getClass();

    function findTokenBy(array $criteria);

    function findTokenByToken($token);

    function updateToken(OAuth2TokenInterface $token, $andFlush = true);

    function deleteToken(OAuth2TokenInterface $token);
}

