<?php

/*
 * This file is part of the FOSOAuthServerBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\OAuthServerBundle\Storage;

use FOS\OAuthServerBundle\Model\AccessTokenManagerInterface;
use FOS\OAuthServerBundle\Model\RefreshTokenManagerInterface;
use FOS\OAuthServerBundle\Model\AuthCodeManagerInterface;
use FOS\OAuthServerBundle\Model\ClientManagerInterface;
use FOS\OAuthServerBundle\Model\ClientInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use OAuth2\IOAuth2RefreshTokens;
use OAuth2\IOAuth2GrantUser;
use OAuth2\IOAuth2GrantCode;
use OAuth2\Model\IOAuth2Client;

class OAuthStorage implements IOAuth2RefreshTokens, IOAuth2GrantUser, IOAuth2GrantCode
{
    /**
     * @var \FOS\OAuthServerBundle\Model\ClientManagerInterface
     */
    protected $clientManager;

    /**
     * @var \FOS\OAuthServerBundle\Model\AccessTokenManagerInterface
     */
    protected $accessTokenManager;

    /**
     * @var \FOS\OAuthServerBundle\Model\RefreshTokenManagerInterface
     */
    protected $refreshTokenManager;

    /**
     * @var \FOS\OAuthServerBundle\Model\AuthCodeManagerInterface;
     */
    protected $authCodeManager;

    /**
     * @var \Symfony\Component\Security\Core\User\UserProviderInterface
     */
    protected $userProvider;

    /**
     * @var \Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface
     */
    protected $encoderFactory;

    /**
     * @param \FOS\OAuthServerBundle\Model\ClientManagerInterface $clientManager
     * @param \FOS\OAuthServerBundle\Model\AccessTokenManagerInterface $accessTokenManager
     * @param \FOS\OAuthServerBundle\Model\RefreshTokenManagerInterface $refreshTokenManager
     * @param \FOS\OAuthServerBundle\Model\AuthCodeManagerInterface $authCodeManager
     * @param null|\Symfony\Component\Security\Core\User\UserProviderInterface $userProvider
     * @param null|\Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface $encoderFactory
     */
    public function __construct(ClientManagerInterface $clientManager, AccessTokenManagerInterface $accessTokenManager,
                RefreshTokenManagerInterface $refreshTokenManager, AuthCodeManagerInterface $authCodeManager, UserProviderInterface $userProvider = null, EncoderFactoryInterface $encoderFactory = null)
    {
        $this->clientManager = $clientManager;
        $this->accessTokenManager = $accessTokenManager;
        $this->refreshTokenManager = $refreshTokenManager;
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
        if (!$client instanceof ClientInterface) {
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
        if (!$client instanceof ClientInterface) {
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
        if (!$client instanceof ClientInterface) {
            throw new \InvalidArgumentException;
        }

        return in_array($grant_type, $client->getAllowedGrantTypes(), true);
    }

    public function checkUserCredentials(IOAuth2Client $client, $username, $password)
    {
        if (!$client instanceof ClientInterface) {
            throw new \InvalidArgumentException;
        }

        try {
            $user = $this->userProvider->loadUserByUsername($username);
        } catch(AuthenticationException $e) {
            return false;
        }

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
        if (!$client instanceof ClientInterface) {
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

    /**
     * {@inheritdoc}
     */
    public function getRefreshToken($tokenString)
    {
        return $this->refreshTokenManager->findTokenByToken($tokenString);
    }

    /**
     * {@inheritdoc}
     */
    public function createRefreshToken($tokenString, IOAuth2Client $client, $data, $expires, $scope = NULL)
    {
        if (!$client instanceof ClientInterface) {
            throw new \InvalidArgumentException;
        }

        $token = $this->refreshTokenManager->createToken();
        $token->setToken($tokenString);
        $token->setClient($client);
        $token->setData($data);
        $token->setExpiresAt($expires);
        $token->setScope($scope);
        $this->refreshTokenManager->updateToken($token);

        return $token;
    }

    /**
     * {@inheritdoc}
     */
    public function unsetRefreshToken($tokenString)
    {
        $token = $this->refreshTokenManager->findTokenByToken($tokenString);

        if (null !== $token) {
            $this->refreshTokenManager->deleteToken($token);
        }
    }
}
