<?php

namespace Alb\OAuth2ServerBundle\Entity;

use Alb\OAuth2ServerBundle\Entity\OAuth2TokenManager;
use Alb\OAuth2ServerBundle\Model\OAuth2AccessTokenManagerInterface;

class OAuth2AccessTokenManager extends OAuth2TokenManager implements OAuth2AccessTokenManagerInterface
{
}

