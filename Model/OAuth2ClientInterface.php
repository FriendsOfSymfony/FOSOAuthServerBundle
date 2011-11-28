<?php

namespace Alb\OAuth2ServerBundle\Model;

use OAuth2\Model\IOAuth2Client;

interface OAuth2ClientInterface extends IOAuth2Client
{
    function getId();

    function setRandomId($random);

    function getRandomId();

    function setSecret($secret);

    function checkSecret($secret);

    function getSecret();

    function setRedirectUris(array $redirectUris);

    function setAllowedGrantTypes(array $grantTypes);

    function getAllowedGrantTypes();
}

