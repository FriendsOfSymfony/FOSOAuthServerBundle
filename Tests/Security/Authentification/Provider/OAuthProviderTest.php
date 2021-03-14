<?php

/*
 * This file is part of the FOSOAuthServerBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\OAuthServerBundle\Tests\Security\Authentication\Provider;

use FOS\OAuthServerBundle\Model\AccessToken;
use FOS\OAuthServerBundle\Security\Authentication\Provider\OAuthProvider;
use FOS\OAuthServerBundle\Security\Authentication\Token\OAuthToken;
use OAuth2\OAuth2;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class OAuthProviderTest extends TestCase
{
    /**
     * @var \\PHPUnit\Framework\MockObject\MockObject|UserInterface
     */
    protected $user;

    /**
     * @var \\PHPUnit\Framework\MockObject\MockObject|UserProviderInterface
     */
    protected $userProvider;

    /**
     * @var \\PHPUnit\Framework\MockObject\MockObject|OAuthProvider
     */
    protected $provider;

    /**
     * @var \\PHPUnit\Framework\MockObject\MockObject|OAuth2
     */
    protected $serverService;

    /**
     * @var UserCheckerInterface
     */
    protected $userChecker;

    public function setUp(): void
    {
        $this->user = $this->getMockBuilder(UserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->userProvider = $this->getMockBuilder(UserProviderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->serverService = $this->getMockBuilder(OAuth2::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->userChecker = $this->getMockBuilder(UserCheckerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->provider = new OAuthProvider($this->userProvider, $this->serverService, $this->userChecker);
    }

    public function testAuthenticateReturnsTokenIfValid(): void
    {
        $token = new OAuthToken();
        $token->setToken('x');

        $this->user->expects(self::once())
            ->method('getRoles')
            ->willReturn(array('ROLE_USER'));

        $accessToken = new AccessToken();
        $accessToken->setUser($this->user);

        $this->serverService->expects(self::once())
            ->method('verifyAccessToken')
            ->with('x')
            ->willReturn($accessToken);

        $result = $this->provider->authenticate($token);

        self::assertSame($this->user, $result->getUser());
        self::assertEquals($token->getToken(), $result->getToken());
        self::assertTrue($result->isAuthenticated());
        self::assertCount(1, $result->getRoles());

        $roles = $result->getRoles();
        self::assertEquals('ROLE_USER', $roles[0]->getRole());
    }

    public function testAuthenticateReturnsTokenIfValidEvenIfNullData(): void
    {
        $token = new OAuthToken();
        $token->setToken('x');

        $accessToken = new AccessToken();

        $this->serverService->expects(self::once())
            ->method('verifyAccessToken')
            ->with('x')
            ->willReturn($accessToken);

        $result = $this->provider->authenticate($token);

        self::assertNull($result->getUser());
        self::assertTrue($result->isAuthenticated());
        self::assertCount(0, $result->getRoles());
    }

    public function testAuthenticateTransformsScopesAsRoles(): void
    {
        $token = new OAuthToken();
        $token->setToken('x');

        $accessToken = new AccessToken();
        $accessToken->setScope('foo bar');

        $this->serverService->expects(self::once())
            ->method('verifyAccessToken')
            ->with('x')
            ->willReturn($accessToken);

        $result = $this->provider->authenticate($token);

        self::assertNull($result->getUser());
        self::assertTrue($result->isAuthenticated());

        $roles = $result->getRoles();
        self::assertCount(2, $roles);
        self::assertInstanceOf(Role::class, $roles[0]);
        self::assertEquals('ROLE_FOO', $roles[0]->getRole());
        self::assertInstanceOf(Role::class, $roles[1]);
        self::assertEquals('ROLE_BAR', $roles[1]->getRole());
    }

    public function testAuthenticateWithNullScope(): void
    {
        $token = new OAuthToken();
        $token->setToken('x');

        $accessToken = new AccessToken();
        $accessToken->setScope(null);

        $this->serverService->expects(self::once())
            ->method('verifyAccessToken')
            ->with('x')
            ->willReturn($accessToken);

        $result = $this->provider->authenticate($token);

        self::assertNull($result->getUser());
        self::assertTrue($result->isAuthenticated());

        $roles = $result->getRoles();
        self::assertCount(0, $roles);
    }

    public function testAuthenticateWithEmptyScope(): void
    {
        $token = new OAuthToken();
        $token->setToken('x');

        $accessToken = new AccessToken();
        $accessToken->setScope('');

        $this->serverService->expects(self::once())
            ->method('verifyAccessToken')
            ->with('x')
            ->willReturn($accessToken);

        $result = $this->provider->authenticate($token);

        self::assertNull($result->getUser());
        self::assertTrue($result->isAuthenticated());

        $roles = $result->getRoles();
        self::assertCount(0, $roles);
    }
}
