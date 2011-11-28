<?php

namespace Alb\OAuth2ServerBundle\Service;

use OAuth2\IOAuth2Storage;
use Alb\OAuth2ServerBundle\Model\OAuth2ClientManagerInterface;
use OAuth2\Model\IOAuth2Client;
use Alb\OAuth2ServerBundle\Model\OAuth2ClientInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use OAuth2\IOAuth2GrantUser;
use Alb\OAuth2ServerBundle\Model\OAuth2AccessTokenManagerInterface;

class OAuth2StorageService implements IOAuth2Storage, IOAuth2GrantUser
{
    protected $clientManager;

    protected $accessTokenManager;

    protected $userProvider;

    protected $encoderFactory;

    public function __construct(OAuth2ClientManagerInterface $clientManager, OAuth2AccessTokenManagerInterface $accessTokenManager, UserProviderInterface $userProvider = null, EncoderFactoryInterface $encoderFactory = null)
    {
        $this->clientManager = $clientManager;
        $this->accessTokenManager = $accessTokenManager;
        $this->userProvider = $userProvider;
        $this->encoderFactory = $encoderFactory;
    }

    public function getClient($clientId)
    {
        return $this->clientManager->findClientByPublicId($clientId);
    }

    public function checkClientCredentials(IOAuth2Client $client, $client_secret = null)
    {
        if (!$client instanceof OAuth2ClientInterface) {
            throw new \InvalidArgumentException;
        }

        return $client->checkSecret($client_secret);
    }

    public function getAccessToken($token)
    {
        return $this->accessTokenManager->findTokenByToken($token);
    }

    public function createAccessToken($tokenString, IOAuth2Client $client, $data, $expires, $scope = null)
    {
        if (!$client instanceof OAuth2ClientInterface) {
            throw new \InvalidArgumentException;
        }

        $token = $this->accessTokenManager->createToken();
        $token->setToken($tokenString);
        $token->setClient($client);
        $token->setData($data);
        $token->setExpiresAt($expires);
        $token->setScope($scope);
        $this->accessTokenManager->updateToken($token);

        return $token;
    }

    public function checkRestrictedGrantType(IOAuth2Client $client, $grant_type)
    {
        if (!$client instanceof OAuth2ClientInterface) {
            throw new \InvalidArgumentException;
        }

        return in_array($grant_type, $client->getAllowedGrantTypes(), true);
    }

    public function checkUserCredentials(IOAuth2Client $client, $username, $password)
    {
        if (!$client instanceof OAuth2ClientInterface) {
            throw new \InvalidArgumentException;
        }

        $user = $this->userProvider->loadUserByUsername($username);

        if (!$user) {
            return false;
        }

        $encoder = $this->encoderFactory->getEncoder($user);

        return $encoder->isPasswordValid($user->getPassword(), $password, $user->getSalt());
    }
}

