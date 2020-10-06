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

namespace FOS\OAuthServerBundle\Tests\Security\Authentication\Provider;

use FOS\OAuthServerBundle\Model\AccessToken;
use FOS\OAuthServerBundle\Security\Authentication\Provider\OAuthProvider;
use FOS\OAuthServerBundle\Security\Authentication\Token\OAuthToken;
use OAuth2\OAuth2;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class OAuthProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|UserInterface
     */
    protected $user;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|UserProviderInterface
     */
    protected $userProvider;

    /**
     * @var OAuthProvider
     */
    protected $provider;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|OAuth2
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
            ->getMock()
        ;
        $this->userProvider = $this->getMockBuilder(UserProviderInterface::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->serverService = $this->getMockBuilder(OAuth2::class)
            ->disableOriginalConstructor()
            ->setMethods(['verifyAccessToken'])
            ->getMock()
        ;
        $this->userChecker = $this->getMockBuilder(UserCheckerInterface::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->provider = new OAuthProvider($this->userProvider, $this->serverService, $this->userChecker);
    }

    public function testAuthenticateReturnsTokenIfValid(): void
    {
        $token = new OAuthToken();
        $token->setToken('x');

        $this->user->expects($this->once())
            ->method('getRoles')
            ->will($this->returnValue(['ROLE_USER']))
        ;

        $accessToken = new AccessToken();
        $accessToken->setUser($this->user);

        $this->serverService->expects($this->once())
            ->method('verifyAccessToken')
            ->with('x')
            ->will($this->returnValue($accessToken))
        ;

        $result = $this->provider->authenticate($token);
        $roles = $result->getRoleNames();

        $this->assertSame($this->user, $result->getUser());
        $this->assertSame($token->getToken(), $result->getToken());
        $this->assertTrue($result->isAuthenticated());
        $this->assertCount(1, $roles);
        $this->assertSame('ROLE_USER', $roles[0]);
    }

    public function testAuthenticateReturnsTokenIfValidEvenIfNullData(): void
    {
        $token = new OAuthToken();
        $token->setToken('x');

        $accessToken = new AccessToken();

        $this->serverService->expects($this->once())
            ->method('verifyAccessToken')
            ->with('x')
            ->will($this->returnValue($accessToken))
        ;

        $result = $this->provider->authenticate($token);

        $this->assertNull($result->getUser());
        $this->assertTrue($result->isAuthenticated());
        $this->assertCount(0, $result->getRoleNames());
    }

    public function testAuthenticateTransformsScopesAsRoles(): void
    {
        $token = new OAuthToken();
        $token->setToken('x');

        $accessToken = new AccessToken();
        $accessToken->setScope('foo bar');

        $this->serverService->expects($this->once())
            ->method('verifyAccessToken')
            ->with('x')
            ->will($this->returnValue($accessToken))
        ;

        $result = $this->provider->authenticate($token);

        $this->assertNull($result->getUser());
        $this->assertTrue($result->isAuthenticated());

        $roles = $result->getRoleNames();
        $this->assertCount(2, $roles);
        $this->assertSame('ROLE_FOO', $roles[0]);
        $this->assertSame('ROLE_BAR', $roles[1]);
    }

    public function testAuthenticateWithNullScope(): void
    {
        $this->markTestIncomplete('Scope is not nullable');

        $token = new OAuthToken();
        $token->setToken('x');

        $accessToken = new AccessToken();
        // $accessToken->setScope(null);

        $this->serverService->expects($this->once())
            ->method('verifyAccessToken')
            ->with('x')
            ->will($this->returnValue($accessToken))
        ;

        $result = $this->provider->authenticate($token);

        $this->assertNull($result->getUser());
        $this->assertTrue($result->isAuthenticated());
        $this->assertCount(0, $result->getRoleNames());
    }

    public function testAuthenticateWithEmptyScope(): void
    {
        $token = new OAuthToken();
        $token->setToken('x');

        $accessToken = new AccessToken();
        $accessToken->setScope('');

        $this->serverService->expects($this->once())
            ->method('verifyAccessToken')
            ->with('x')
            ->will($this->returnValue($accessToken))
        ;

        $result = $this->provider->authenticate($token);

        $this->assertNull($result->getUser());
        $this->assertTrue($result->isAuthenticated());
        $this->assertCount(0, $result->getRoleNames());
    }
}
