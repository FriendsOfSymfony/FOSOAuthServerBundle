<?php

namespace Alb\OAuth2ServerBundle\Security\Authentification\Provider;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

use Alb\OAuth2ServerBundle\Model\Provider\OAuth2TokenProviderInterface;
use Alb\OAuth2ServerBundle\Security\Authentification\Token\OAuth2Token;
use OAuth2\OAuth2;
use OAuth2\OAuth2ServerException;

/**
 * OAuth2Provider class.
 *
 * @package     AlbOAuth2ServerBundle
 * @subpackage  Security
 * @author  Arnaud Le Blanc <arnaud.lb@gmail.com>
 */
class OAuth2Provider implements AuthenticationProviderInterface
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
                    return $token;
                }
            }
        } catch(OAuth2ServerException $e) {
            throw new AuthenticationException('OAuth2 authentification failed', null, 0, $e);
        }

        throw new AuthenticationException('OAuth2 authentification failed');
    }

    /**
     * {@inheritdoc}
     */
    public function supports(TokenInterface $token)
    {
        return ($token instanceof OAuth2Token);
    }
}
