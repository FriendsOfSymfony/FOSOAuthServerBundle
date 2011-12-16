<?php

namespace FOS\OAuthServerBundle\Model;

use OAuth2\Model\IOAuth2Token;
use FOS\OAuthServerBundle\Model\OAuth2ClientInterface;
use FOS\OAuthServerBundle\Model\OAuth2TokenInterface;

interface OAuth2TokenInterface extends IOAuth2Token
{
    function getId();

    function setExpiresAt($timestamp);

    function getExpiresAt();

    function setToken($token);

    function setScope($scope);

    function setData($data);

    function setClient(OAuth2ClientInterface $client);

    function getClient();
}


