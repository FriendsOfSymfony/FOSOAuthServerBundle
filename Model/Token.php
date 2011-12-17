<?php

namespace FOS\OAuthServerBundle\Model;

class Token implements TokenInterface
{
    protected $id;

    protected $client;

    protected $token;

    protected $expiresAt;

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

    public function setToken($token)
    {
        $this->token = $token;
    }

    public function getToken()
    {
        return $this->token;
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

    public function setClient(ClientInterface $client)
    {
        $this->client = $client;
    }

    public function getClient()
    {
        return $this->client;
    }
}

