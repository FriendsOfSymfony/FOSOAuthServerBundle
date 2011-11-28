<?php

namespace Alb\OAuth2ServerBundle\Model;

use OAuth2\OAuth2;
use Alb\OAuth2ServerBundle\Util\Random;

class OAuth2Client implements OAuth2ClientInterface
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

