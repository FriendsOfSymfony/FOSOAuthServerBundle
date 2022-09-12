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
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AccountStatusException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
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
     * @var UserCheckerInterface
     */
    protected $userChecker;

    /**
     * @var UserProviderInterface
     */
    protected $userProvider;

    public function __construct(
        OAuth2 $serverService,
        UserCheckerInterface $userChecker,
        UserProviderInterface $userProvider
    ) {
        $this->serverService = $serverService;
        $this->userChecker = $userChecker;
        $this->userProvider = $userProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate(Request $request): UserPassportInterface
    {
        // remove the authorization header from the request on this check
        $tokenString = $this->serverService->getBearerToken($request, true);
        $accessToken = $scope = $user = $username = null;

        try {
            $accessToken = $this->serverService->verifyAccessToken($tokenString);
            $scope = $accessToken->getScope();
            $user = $accessToken->getUser();
            // allow for dependency on deprecated getUsername method
            $username = $user instanceof UserInterface
                ? (method_exists($user, 'getUserIdentifier') ? $user->getUserIdentifier() : $user->getUsername())
                : null
            ;
        } catch (OAuth2AuthenticateException $e) {
            // do nothing - credentials will remain unresolved below
        }

        // configure the passport badges, ensuring requisite string types
        $userBadge = new UserBadge($username ?? '');
        $credentials = new OAuthCredentials($tokenString ?? '', $scope ?? '');

        // check the user if not null
        if ($user instanceof UserInterface) {
            try {
                $this->userChecker->checkPreAuth($user);

                // mark the credentials as resolved
                $credentials->markResolved();
            } catch (AccountStatusException $e) {
                // do nothing - credentials remain unresolved
            }
        }

        // passport will only be valid if all badges are resolved (user badge
        // is always resolved, credentials badge if passing the above check)
        return new Passport($userBadge, $credentials);
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
                throw new OAuth2AuthenticateException((string) Response::HTTP_UNAUTHORIZED, OAuth2::TOKEN_TYPE_BEARER, $this->serverService->getVariable(OAuth2::CONFIG_WWW_REALM), 'access_denied', 'Unexpected credentials type.');
            }

            // get the passport badges
            $credentials = $passport->getBadge(OAuthCredentials::class);
            $user = $this->userProvider->loadUserByIdentifier(
                $passport->getBadge(UserBadge::class)->getUserIdentifier()
            );

            // check the user
            try {
                $this->userChecker->checkPostAuth($user);
            } catch (AccountStatusException $e) {
                throw new OAuth2AuthenticateException((string) Response::HTTP_UNAUTHORIZED, OAuth2::TOKEN_TYPE_BEARER, $this->serverService->getVariable(OAuth2::CONFIG_WWW_REALM), 'access_denied', $e->getMessage());
            }
        } catch (OAuth2ServerException $e) {
            throw new AuthenticationException('OAuth2 authentication failed', 0, $e);
        }

        $token = new OAuthToken($credentials->getRoles($user));
        $token->setAuthenticated(true);
        $token->setToken($credentials->getTokenString());
        $token->setUser($user);

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
            (string) Response::HTTP_UNAUTHORIZED,
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
        // do not remove the authorization header from the request on this check
        $tokenString = $this->serverService->getBearerToken($request);

        return is_string($tokenString) && !empty($tokenString);
    }
}
