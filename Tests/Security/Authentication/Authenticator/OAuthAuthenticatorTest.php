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

namespace FOS\OAuthServerBundle\Tests\Security\Authentication\Authenticator;

use FOS\OAuthServerBundle\Model\AccessToken;
use FOS\OAuthServerBundle\Security\Authentication\Authenticator\OAuthAuthenticator;
use FOS\OAuthServerBundle\Security\Authentication\Passport\OAuthCredentials;
use OAuth2\OAuth2;
use OAuth2\OAuth2AuthenticateException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CredentialsExpiredException;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

class OAuthAuthenticatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var OAuthAuthenticator
     */
    protected $authenticator;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|OAuth2
     */
    protected $serverService;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|User
     */
    protected $user;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|UserCheckerInterface
     */
    protected $userChecker;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|UserProviderInterface
     */
    protected $userProvider;

    public function setUp(): void
    {
        $this->serverService = $this->getMockBuilder(OAuth2::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getBearerToken',
                'getVariable',
                'verifyAccessToken',
            ])
            ->getMock()
        ;
        $this->userChecker = $this->getMockBuilder(UserCheckerInterface::class)->disableOriginalConstructor()->getMock();
        $this->userProvider = $this->getMockBuilder(UserProviderInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'loadUserByIdentifier',
                'loadUserByUsername',
                'refreshUser',
                'supportsClass',
            ])
            ->getMock()
        ;

        // mock the core user object rather than the user interface that the new
        // getUserIdentifier method is used rather than the deprecated getUsername
        $this->user = $this->getMockBuilder(User::class)->disableOriginalConstructor()->getMock();

        $this->authenticator = new OAuthAuthenticator(
            $this->serverService,
            $this->userChecker,
            $this->userProvider
        );
    }

    public function testAuthenticateReturnsPassportIfValid(): void
    {
        // expect the OAuth2 service to get the token from the request header,
        // flagging the authorization header to be removed at the same time
        $this->serverService->expects($this->once())
            ->method('getBearerToken')
            ->with(
                $this->isInstanceOf(Request::class),
                $this->equalTo(true)
            )
            ->will($this->returnValue('mock_token_string'))
        ;

        // expect the OAuth2 service to verify the token, returning an access token
        $accessToken = new AccessToken();
        $accessToken->setUser($this->user);
        $accessToken->setScope('scope_1 scope_2');
        $this->serverService->expects($this->once())
            ->method('verifyAccessToken')
            ->with('mock_token_string')
            ->will($this->returnValue($accessToken))
        ;

        // expect the username from the user
        $this->user->expects($this->once())
            ->method('getUserIdentifier')
            ->will($this->returnValue('test_user'))
        ;

        // expect the user checker to pass
        $this->userChecker->expects($this->once())
            ->method('checkPreAuth')
            ->with($this->user)
        ;

        $passport = $this->authenticator->authenticate(new Request());

        $this->assertInstanceOf(Passport::class, $passport);
        $this->assertCount(2, $passport->getBadges());
        $this->assertTrue($passport->hasBadge(OAuthCredentials::class));
        $this->assertTrue($passport->hasBadge(UserBadge::class));
        $this->assertSame('test_user', $passport->getBadge(UserBadge::class)->getUserIdentifier());
        $this->assertSame('mock_token_string', $passport->getBadge(OAuthCredentials::class)->getTokenString());
        $this->assertSame(['ROLE_SCOPE_1', 'ROLE_SCOPE_2'], $passport->getBadge(OAuthCredentials::class)->getRoles($this->user));
        $this->assertTrue($passport->getBadge(OAuthCredentials::class)->isResolved());
    }

    public function testAuthenticateReturnsUnresolvedPassportWhenNullUser(): void
    {
        // expect the OAuth2 service to get the token from the request header,
        // flagging the authorization header to be removed at the same time
        $this->serverService->expects($this->once())
            ->method('getBearerToken')
            ->with(
                $this->isInstanceOf(Request::class),
                $this->equalTo(true)
            )
            ->will($this->returnValue('mock_token_string'))
        ;

        // expect the OAuth2 service to verify the token, returning an access
        // token, but without a related user
        $accessToken = new AccessToken();
        $this->serverService->expects($this->once())
            ->method('verifyAccessToken')
            ->with('mock_token_string')
            ->will($this->returnValue($accessToken))
        ;

        // expect the null user value to not be processed
        $this->userChecker->expects($this->never())->method('checkPreAuth');

        $passport = $this->authenticator->authenticate(new Request());

        // confirm that the returned passport won't pass validation
        $this->assertFalse($passport->getBadge(OAuthCredentials::class)->isResolved());
    }

    public function testAuthenticateReturnsUnresolvedPassportWhenInvalidToken(): void
    {
        // expect the OAuth2 service to get the token from the request header,
        // flagging the authorization header to be removed at the same time
        $this->serverService->expects($this->once())
            ->method('getBearerToken')
            ->with(
                $this->isInstanceOf(Request::class),
                $this->equalTo(true)
            )
            ->will($this->returnValue('mock_token_string'))
        ;

        // expect the OAuth2 service to not verify the token, throwing an exception
        $this->serverService->expects($this->once())
            ->method('verifyAccessToken')
            ->with('mock_token_string')
            ->willThrowException(new OAuth2AuthenticateException(
                (string) Response::HTTP_UNAUTHORIZED,
                'mock_token_type',
                'mock_realm',
                'invalid_grant',
                'The access token provided is invalid.',
                null
            ))
        ;

        // expect the null user value to not be processed
        $this->userChecker->expects($this->never())->method('checkPreAuth');

        $passport = $this->authenticator->authenticate(new Request());

        // confirm that the returned passport won't pass validation
        $this->assertFalse($passport->getBadge(OAuthCredentials::class)->isResolved());
    }

    public function testAuthenticateTransformsAccountStatusException(): void
    {
        // expect the OAuth2 service to get the token from the request header,
        // flagging the authorization header to be removed at the same time
        $this->serverService->expects($this->once())
            ->method('getBearerToken')
            ->with(
                $this->isInstanceOf(Request::class),
                $this->equalTo(true)
            )
            ->will($this->returnValue('mock_token_string'))
        ;

        // expect the OAuth2 service to verify the token, returning an access token
        $accessToken = new AccessToken();
        $accessToken->setUser($this->user);
        $accessToken->setScope('scope_1 scope_2');
        $this->serverService->expects($this->once())
            ->method('verifyAccessToken')
            ->with('mock_token_string')
            ->will($this->returnValue($accessToken))
        ;

        // expect the user checker to not pass
        $this->userChecker->expects($this->once())
            ->method('checkPreAuth')
            ->with($this->user)
            ->willThrowException(new DisabledException('User account is disabled.'))
        ;

        $passport = $this->authenticator->authenticate(new Request());

        // confirm that the returned passport won't pass validation
        $this->assertFalse($passport->getBadge(OAuthCredentials::class)->isResolved());
    }

    public function testCreateAuthenticatedTokenWithValidPassport(): void
    {
        // expect the user to be loaded by the provider
        $this->userProvider->expects($this->once())
            ->method('loadUserByIdentifier')
            ->with('test_user')
            ->will($this->returnValue($this->user))
        ;

        // expect the user checker to pass
        $this->userChecker->expects($this->once())
            ->method('checkPostAuth')
            ->with($this->user)
        ;

        // configure roles on the user to confirm they are combined with the
        // access token scopes
        $this->user->expects($this->once())
            ->method('getRoles')
            ->will($this->returnValue(['ROLE_USER']))
        ;

        // configure the passport
        $passport = new Passport(
            new UserBadge('test_user'),
            new OAuthCredentials('mock_token_string', 'scope_1 scope_2')
        );

        $token = $this->authenticator->createAuthenticatedToken($passport, 'api_firewall_name');

        $this->assertTrue($token->isAuthenticated());
        $this->assertSame('mock_token_string', $token->getToken());
        $this->assertSame($this->user, $token->getUser());
        $this->assertSame(['ROLE_USER', 'ROLE_SCOPE_1', 'ROLE_SCOPE_2'], $token->getRoleNames());
    }

    public function testCreateAuthenticatedTokenWithUnexpectedPassportCredentials(): void
    {
        // expect the user to not be loaded by the provider
        $this->userProvider->expects($this->never())->method('loadUserByIdentifier');

        // configure the passport with non-oauth credentials
        $passport = new Passport(
            new UserBadge('test_user'),
            new PasswordCredentials('mock_password')
        );

        // expect an authentication exception
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('OAuth2 authentication failed');

        $this->authenticator->createAuthenticatedToken($passport, 'api_firewall_name');
    }

    public function testCreateAuthenticatedTokenTransformsAccountStatusException(): void
    {
        // expect the user to be loaded by the provider
        $this->userProvider->expects($this->once())
            ->method('loadUserByIdentifier')
            ->with('test_user')
            ->will($this->returnValue($this->user))
        ;

        // expect the user checker to not pass
        $this->userChecker->expects($this->once())
            ->method('checkPostAuth')
            ->with($this->user)
            ->willThrowException(new CredentialsExpiredException('User credentials have expired.'))
        ;

        // configure the passport
        $passport = new Passport(
            new UserBadge('test_user'),
            new OAuthCredentials('mock_token_string', 'scope')
        );

        // expect an authentication exception
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('OAuth2 authentication failed');

        $this->authenticator->createAuthenticatedToken($passport, 'api_firewall_name');
    }

    public function testOnAuthenticationFailure(): void
    {
        // expect the OAuth server to get the realm
        $this->serverService->expects($this->once())
            ->method('getVariable')
            ->with('realm')
            ->will($this->returnValue('mock_realm'))
        ;

        $response = $this->authenticator->onAuthenticationFailure(
            new Request(),
            new AuthenticationException('Authentication failure message')
        );

        $this->assertSame(401, $response->getStatusCode());
        $this->assertSame('{"error":"access_denied","error_description":"Authentication failure message"}', $response->getContent());
    }
}
