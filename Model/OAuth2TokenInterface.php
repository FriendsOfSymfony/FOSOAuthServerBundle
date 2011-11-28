<?php

namespace Alb\OAuth2ServerBundle\Model;

use OAuth2\Model\IOAuth2Token;
use Alb\OAuth2ServerBundle\Model\OAuth2ClientInterface;
use Alb\OAuth2ServerBundle\Model\OAuth2TokenInterface;

interface OAuth2TokenInterface extends IOAuth2Token
{
    function getId();

    function setExpiresAt($timestamp);

    function setToken($token);

    function setScope($scope);

    function setData($data);

    function setClient(OAuth2ClientInterface $client);

    function getClient();
}


