<?php

declare(strict_types=1);

/*
 * This file is part of the FOSOAuthServerBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\OAuthServerBundle\Security\Authentication\Provider;

use FOS\OAuthServerBundle\Security\Authentication\Token\OAuthToken;
use OAuth2\OAuth2;
use OAuth2\OAuth2AuthenticateException;
use OAuth2\OAuth2ServerException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AccountStatusException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * OAuthProvider class.
 *
 * @author  Arnaud Le Blanc <arnaud.lb@gmail.com>
 */
class OAuthProvider implements AuthenticationProviderInterface
{
    /**
     * @var UserProviderInterface
     */
    protected $userProvider;
    /**
     * @var OAuth2
     */
    protected $serverService;
    /**
     * @var UserCheckerInterface
     */
    protected $userChecker;

    /**
     * @param UserProviderInterface $userProvider  the user provider
     * @param OAuth2                $serverService the OAuth2 server service
     * @param UserCheckerInterface  $userChecker   The Symfony User Checker for Pre and Post auth checks
     */
    public function __construct(UserProviderInterface $userProvider, OAuth2 $serverService, UserCheckerInterface $userChecker)
    {
        $this->userProvider = $userProvider;
        $this->serverService = $serverService;
        $this->userChecker = $userChecker;
    }

    /**
     * @param OAuthToken&TokenInterface $token
     *
     * @return OAuthToken|null
     */
    public function authenticate(TokenInterface $token)
    {
        if (!$this->supports($token)) {
            // note: since strict types in PHP 7, return; and return null; are not the same
            // Symfony's interface says to "never return null", but return; is still technically null
            // PHPStan treats return; as return (void);
            return null;
        }

        try {
            $tokenString = $token->getToken();

            // TODO: this is nasty, create a proper interface here
            /** @var OAuthToken&TokenInterface&\OAuth2\Model\IOAuth2AccessToken $accessToken */
            $accessToken = $this->serverService->verifyAccessToken($tokenString);

            $scope = $accessToken->getScope();
            $user = $accessToken->getUser();

            if (null !== $user) {
                try {
                    $this->userChecker->checkPreAuth($user);
                } catch (AccountStatusException $e) {
                    throw new OAuth2AuthenticateException(Response::HTTP_UNAUTHORIZED, OAuth2::TOKEN_TYPE_BEARER, $this->serverService->getVariable(OAuth2::CONFIG_WWW_REALM), 'access_denied', $e->getMessage());
                }

                $token->setUser($user);
            }

            $roles = (null !== $user) ? $user->getRoles() : [];

            if (!empty($scope)) {
                foreach (explode(' ', $scope) as $role) {
                    $roles[] = 'ROLE_'.mb_strtoupper($role);
                }
            }

            $roles = array_unique($roles, SORT_REGULAR);

            $token = new OAuthToken($roles);
            $token->setAuthenticated(true);
            $token->setToken($tokenString);

            if (null !== $user) {
                try {
                    $this->userChecker->checkPostAuth($user);
                } catch (AccountStatusException $e) {
                    throw new OAuth2AuthenticateException(Response::HTTP_UNAUTHORIZED, OAuth2::TOKEN_TYPE_BEARER, $this->serverService->getVariable(OAuth2::CONFIG_WWW_REALM), 'access_denied', $e->getMessage());
                }

                $token->setUser($user);
            }

            return $token;
        } catch (OAuth2ServerException $e) {
            throw new AuthenticationException('OAuth2 authentication failed', 0, $e);
        }

        throw new AuthenticationException('OAuth2 authentication failed');
    }

    /**
     * {@inheritdoc}
     */
    public function supports(TokenInterface $token)
    {
        return $token instanceof OAuthToken;
    }
}
