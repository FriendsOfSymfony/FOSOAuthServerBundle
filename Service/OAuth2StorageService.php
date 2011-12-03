<?php

namespace Alb\OAuth2ServerBundle\Service;

use OAuth2\IOAuth2Storage;
use Alb\OAuth2ServerBundle\Model\OAuth2ClientManagerInterface;
use OAuth2\Model\IOAuth2Client;
use Alb\OAuth2ServerBundle\Model\OAuth2ClientInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use OAuth2\IOAuth2GrantUser;
use OAuth2\IOAuth2GrantCode;
use Alb\OAuth2ServerBundle\Model\OAuth2AccessTokenManagerInterface;
use Alb\OAuth2ServerBundle\Model\OAuth2AuthCodeManagerInterface;

class OAuth2StorageService implements IOAuth2Storage, IOAuth2GrantUser, IOAuth2GrantCode
{
    /**
     * @var \Alb\OAuth2ServerBundle\Model\OAuth2ClientManagerInterface
     */
    protected $clientManager;

    /**
     * @var \Alb\OAuth2ServerBundle\Model\OAuth2AccessTokenManagerInterface
     */
    protected $accessTokenManager;

    /**
     * @var \Alb\OAuth2ServerBundle\Model\OAuth2AuthCodeManagerInterface;
     */
    protected $authCodeManager;

    /**
     * @var null|\Symfony\Component\Security\Core\User\UserProviderInterface
     */
    protected $userProvider;

    /**
     * @var null|\Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface
     */
    protected $encoderFactory;

    /**
     * @param \Alb\OAuth2ServerBundle\Model\OAuth2ClientManagerInterface $clientManager
     * @param \Alb\OAuth2ServerBundle\Model\OAuth2AccessTokenManagerInterface $accessTokenManager
     * @param \Alb\OAuth2ServerBundle\Model\OAuth2AuthCodeManagerInterface $authCodeManager
     * @param null|\Symfony\Component\Security\Core\User\UserProviderInterface $userProvider
     * @param null|\Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface $encoderFactory
     */
    public function __construct(OAuth2ClientManagerInterface $clientManager, OAuth2AccessTokenManagerInterface $accessTokenManager, OAuth2AuthCodeManagerInterface $authCodeManager, UserProviderInterface $userProvider = null, EncoderFactoryInterface $encoderFactory = null)
    {
        $this->clientManager = $clientManager;
        $this->accessTokenManager = $accessTokenManager;
        $this->authCodeManager = $authCodeManager;
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

        if ($encoder->isPasswordValid($user->getPassword(), $password, $user->getSalt())) {
            return array(
                'data' => $user,
            );
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthCode($code)
    {
        return $this->authCodeManager->findAuthCodeByToken($code);
    }

    /**
     * {@inheritdoc}
     */
    public function createAuthCode($code, IOAuth2Client $client, $data, $redirect_uri, $expires, $scope = NULL)
    {
        if (!$client instanceof OAuth2ClientInterface) {
            throw new \InvalidArgumentException;
        }

        $authCode = $this->authCodeManager->createAuthCode();
        $authCode->setToken($code);
        $authCode->setClient($client);
        $authCode->setData($data);
        $authCode->setRedirectUri($redirect_uri);
        $authCode->setExpiresAt($expires);
        $authCode->setScope($scope);
        $this->authCodeManager->updateAuthCode($authCode);

        return $authCode;
    }


}

