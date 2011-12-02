<?php
/*
 *
 */

namespace Alb\OAuth2ServerBundle\Model;

/**
 * @author Richard Fullmer <richard.fullmer@opensoftdev.com>
 */
class OAuth2AuthCode implements OAuth2AuthCodeInterface
{
    protected $id;

    protected $client;

    protected $code;

    protected $expiresAt;

    protected $redirectUri;

    protected $scope;

    protected $data;

    public function getId()
    {
        return $this->id;
    }

    public function getClientId()
    {
        return $this->client->getPublicId();
    }

    public function setExpiresAt($timestamp)
    {
        $this->expiresAt = $timestamp;
    }

    public function getExpiresAt()
    {
        return $this->expiresAt;
    }

    public function getExpiresIn()
    {
        if ($this->expiresAt) {
            return $this->expiresAt - time();
        } else {
            return PHP_INT_MAX;
        }
    }

    public function hasExpired()
    {
        if ($this->expiresAt) {
            return time() > $this->expiresAt;
        } else {
            return false;
        }
    }

    public function setCode($code)
    {
        $this->code = $code;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function setScope($scope)
    {
        $this->scope = $scope;
    }

    public function getScope()
    {
        return $this->scope;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setClient(OAuth2ClientInterface $client)
    {
        $this->client = $client;
    }

    public function getClient()
    {
        return $this->client;
    }

    public function setRedirectUri($redirectUri)
    {
        $this->redirectUri = $redirectUri;
    }

    public function getRedirectUri()
    {
        return $this->redirectUri;
    }
}
