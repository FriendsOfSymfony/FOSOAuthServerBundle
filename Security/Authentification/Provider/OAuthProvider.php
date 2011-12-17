<?php

/*
 * This file is part of the FOSOAuthServerBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\OAuthServerBundle\Security\Authentification\Provider;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use FOS\OAuthServerBundle\Security\Authentification\Token\OAuthToken;
use OAuth2\OAuth2;
use OAuth2\OAuth2ServerException;

/**
 * OAuthProvider class.
 *
 * @author  Arnaud Le Blanc <arnaud.lb@gmail.com>
 */
class OAuthProvider implements AuthenticationProviderInterface
{
    /**
     * @var \Symfony\Component\Security\Core\User\UserProviderInterface
     */
    protected $userProvider;
    /**
     * @var \OAuth2\OAuth2
     */
    protected $serverService;

    /**
     * @param \Symfony\Component\Security\Core\User\UserProviderInterface $userProvider      The user provider.
     * @param \OAuth2\OAuth2 $serverService The OAuth2 server service.
     */
    public function __construct(UserProviderInterface $userProvider, OAuth2 $serverService)
    {
        $this->userProvider  = $userProvider;
        $this->serverService = $serverService;
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate(TokenInterface $token)
    {
        if (!$this->supports($token)) {
            return null;
        }

        try {
            $accessToken = $this->serverService->verifyAccessToken($token->getToken());
            if ($accessToken) {
                $data = $accessToken->getData();

                if (null !== $data) {
                    $token->setUser($data);
                }

                $token->setAuthenticated(true);

                return $token;
            }
        } catch (OAuth2ServerException $e) {
            throw new AuthenticationException('OAuth2 authentification failed', null, 0, $e);
        }

        throw new AuthenticationException('OAuth2 authentification failed');
    }

    /**
     * {@inheritdoc}
     */
    public function supports(TokenInterface $token)
    {
        return $token instanceof OAuthToken;
    }
}
