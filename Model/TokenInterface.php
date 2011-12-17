<?php

namespace FOS\OAuthServerBundle\Model;

use OAuth2\Model\IOAuth2Token;

interface TokenInterface extends IOAuth2Token
{
    function getId();

    function setExpiresAt($timestamp);

    function getExpiresAt();

    function setToken($token);

    function setScope($scope);

    function setData($data);

    function setClient(ClientInterface $client);

    function getClient();
}


