<?php

/*
 * This file is part of the FOSOAuthServerBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\OAuthServerBundle\Model;

use OAuth2\OAuth2;
use FOS\OAuthServerBundle\Util\Random;

class Client implements ClientInterface
{
    protected $id;

    protected $randomId;

    protected $redirectUris;

    protected $secret;

    protected $allowedGrantTypes;

    public function __construct()
    {
        $this->redirectUris = array();
        $this->allowedGrantTypes = array(
            OAuth2::GRANT_TYPE_AUTH_CODE,
        );
        $this->randomId = Random::generateToken();
        $this->secret = Random::generateToken();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setRandomId($random)
    {
        $this->randomId = $random;
    }

    public function getRandomId()
    {
        return $this->randomId;
    }

    public function getPublicId()
    {
        return "{$this->getId()}_{$this->getRandomId()}";
    }

    public function setSecret($secret)
    {
        $this->secret = $secret;
    }

    public function getSecret()
    {
        return $this->secret;
    }

    public function checkSecret($secret)
    {
        return $this->secret === NULL || $secret === $this->secret;
    }

    public function setRedirectUris(array $redirectUris)
    {
        $this->redirectUris = $redirectUris;
    }

    public function getRedirectUris()
    {
        return $this->redirectUris;
    }

    public function setAllowedGrantTypes(array $grantTypes)
    {
        $this->allowedGrantTypes = $grantTypes;
    }

    public function getAllowedGrantTypes()
    {
        return $this->allowedGrantTypes;
    }
}
