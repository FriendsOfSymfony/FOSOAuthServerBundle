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

namespace FOS\OAuthServerBundle\Security\Authentication\Authenticator;

use FOS\OAuthServerBundle\Security\Authentication\Passport\OAuthCredentials;
use FOS\OAuthServerBundle\Security\Authentication\Token\OAuthToken;
use OAuth2\OAuth2;
use OAuth2\OAuth2AuthenticateException;
use OAuth2\OAuth2ServerException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AccountStatusException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\AuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\UserPassportInterface;

/**
 * OAuthAuthenticator class.
 *
 * @author  Israel J. Carberry <iisisrael@gmail.com>
 */
class OAuthAuthenticator implements AuthenticatorInterface
{
    /**
     * @var OAuth2
     */
    protected $serverService;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var UserCheckerInterface
     */
    protected $userChecker;

    /**
     * @var UserProviderInterface
     */
    protected $userProvider;

    public function __construct(
        OAuth2 $serverService,
        TokenStorageInterface $tokenStorage,
        UserCheckerInterface $userChecker,
        UserProviderInterface $userProvider
    ) {
        $this->serverService = $serverService;
        $this->tokenStorage = $tokenStorage;
        $this->userChecker = $userChecker;
        $this->userProvider = $userProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate(Request $request): UserPassportInterface
    {
        try {
            $token = $this->tokenStorage->getToken();
            $tokenString = $token->getToken();

            $accessToken = $this->serverService->verifyAccessToken($tokenString);

            /** @var \Symfony\Component\Security\Core\User\UserInterface **/
            $user = $accessToken->getUser();

            if (null === $user) {
                throw new AuthenticationException('OAuth2 authentication failed');
            }

            // check the user
            try {
                $this->userChecker->checkPreAuth($user);
            } catch (AccountStatusException $e) {
                throw new OAuth2AuthenticateException(
                    Response::HTTP_UNAUTHORIZED,
                    OAuth2::TOKEN_TYPE_BEARER,
                    $this->serverService->getVariable(OAuth2::CONFIG_WWW_REALM),
                    'access_denied',
                    $e->getMessage()
                );
            }

            return new Passport(
                new UserBadge($user->getUsername()),
                new OAuthCredentials($tokenString, $accessToken->getScope())
            );
        } catch (OAuth2ServerException $e) {
            throw new AuthenticationException('OAuth2 authentication failed', 0, $e);
        }

        // this should never be reached
        throw new AuthenticationException('OAuth2 authentication failed');
    }

    /**
     * {@inheritdoc}
     */
    public function createAuthenticatedToken(PassportInterface $passport, string $firewallName): TokenInterface
    {
        try {
            // expect the badges in the passport from authenticate method above
            if (!$passport->hasBadge(OAuthCredentials::class)
                || !$passport->hasBadge(UserBadge::class)
            ) {
                throw new OAuth2AuthenticateException(
                    Response::HTTP_UNAUTHORIZED,
                    OAuth2::TOKEN_TYPE_BEARER,
                    $this->serverService->getVariable(OAuth2::CONFIG_WWW_REALM),
                    'access_denied',
                    'Unexpected credentials type.'
                );
            }

            // get the passport badges
            $credentials = $passport->getBadge(OAuthCredentials::class);
            $user = $this->userProvider->loadUserByUsername(
                $passport->getBadge(UserBadge::class)->getUserIdentifier()
            );

            // check the user
            try {
                $this->userChecker->checkPostAuth($user);
            } catch (AccountStatusException $e) {
                throw new OAuth2AuthenticateException(
                    Response::HTTP_UNAUTHORIZED,
                    OAuth2::TOKEN_TYPE_BEARER,
                    $this->serverService->getVariable(OAuth2::CONFIG_WWW_REALM),
                    'access_denied',
                    $e->getMessage()
                );
            }
        } catch (OAuth2ServerException $e) {
            throw new AuthenticationException('OAuth2 authentication failed', 0, $e);
        }

        $token = new OAuthToken($credentials->getRoles($user));
        $token->setAuthenticated(true);
        $token->setToken($credentials->getTokenString());
        $token->setUser($user);

        $credentials->markResolved();

        return $token;
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $responseException = new OAuth2AuthenticateException(
            Response::HTTP_UNAUTHORIZED,
            OAuth2::TOKEN_TYPE_BEARER,
            $this->serverService->getVariable(OAuth2::CONFIG_WWW_REALM),
            'access_denied',
            $exception->getMessage()
        );

        return $responseException->getHttpResponse();
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Request $request): ?bool
    {
        return $this->tokenStorage->getToken() instanceof OAuthToken;
    }
}
