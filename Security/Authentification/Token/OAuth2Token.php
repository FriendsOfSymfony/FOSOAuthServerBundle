<?php

namespace Alb\OAuth2ServerBundle\Security\Authentification\Token;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

/**
 * OAuth2Token class.
 *
 * @package     AlbOAuth2ServerBundle
 * @subpackage  Security
 * @author Arnaud Le Blanc <arnaud.lb@gmail.com>
 */
class OAuth2Token extends AbstractToken
{
    /**
     * @var string
     */
    protected $token;

    public function setToken($token)
    {
        $this->token = $token;
    }

    public function getToken()
    {
        return $this->token;
    }

    public function getCredentials()
    {
        return $this->token;
    }
}

