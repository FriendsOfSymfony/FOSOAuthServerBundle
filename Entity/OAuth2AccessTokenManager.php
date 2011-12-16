<?php

namespace FOS\OAuthServerBundle\Entity;

use FOS\OAuthServerBundle\Entity\OAuth2TokenManager;
use FOS\OAuthServerBundle\Model\OAuth2AccessTokenManagerInterface;

class OAuth2AccessTokenManager extends OAuth2TokenManager implements OAuth2AccessTokenManagerInterface
{
}

