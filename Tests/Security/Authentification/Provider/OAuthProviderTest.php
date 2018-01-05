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

class OAuthProviderTest extends \PHPUnit\Framework\TestCase
{
    protected $user;
    protected $userProvider;
    protected $provider;
    protected $serverService;
    protected $userChecker;

    public function setUp()
    {
        $this->user = $this->getMockBuilder('Symfony\Component\Security\Core\User\UserInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->userProvider = $this->getMockBuilder('Symfony\Component\Security\Core\User\UserProviderInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->serverService = $this->getMockBuilder('OAuth2\OAuth2')
            ->disableOriginalConstructor()
            ->setMethods(['verifyAccessToken'])
            ->getMock();
        $this->userChecker = $this->getMockBuilder('Symfony\Component\Security\Core\User\UserCheckerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->provider = new OAuthProvider($this->userProvider, $this->serverService, $this->userChecker);
    }

    public function testAuthenticateReturnsTokenIfValid()
    {
        $token = new OAuthToken();
        $token->setToken('x');

        $this->user->expects($this->once())
            ->method('getRoles')
            ->will($this->returnValue(['ROLE_USER']));

        $accessToken = new AccessToken();
        $accessToken->setUser($this->user);

        $this->serverService->expects($this->once())
            ->method('verifyAccessToken')
            ->with('x')
            ->will($this->returnValue($accessToken));

        $result = $this->provider->authenticate($token);

        $this->assertSame($this->user, $result->getUser());
        $this->assertSame($token->getToken(), $result->getToken());
        $this->assertTrue($result->isAuthenticated());
        $this->assertCount(1, $result->getRoles());

        $roles = $result->getRoles();
        $this->assertSame('ROLE_USER', $roles[0]->getRole());
    }

    public function testAuthenticateReturnsTokenIfValidEvenIfNullData()
    {
        $token = new OAuthToken();
        $token->setToken('x');

        $accessToken = new AccessToken();

        $this->serverService->expects($this->once())
            ->method('verifyAccessToken')
            ->with('x')
            ->will($this->returnValue($accessToken));

        $result = $this->provider->authenticate($token);

        $this->assertNull($result->getUser());
        $this->assertTrue($result->isAuthenticated());
        $this->assertCount(0, $result->getRoles());
    }

    public function testAuthenticateTransformsScopesAsRoles()
    {
        $token = new OAuthToken();
        $token->setToken('x');

        $accessToken = new AccessToken();
        $accessToken->setScope('foo bar');

        $this->serverService->expects($this->once())
            ->method('verifyAccessToken')
            ->with('x')
            ->will($this->returnValue($accessToken));

        $result = $this->provider->authenticate($token);

        $this->assertNull($result->getUser());
        $this->assertTrue($result->isAuthenticated());

        $roles = $result->getRoles();
        $this->assertCount(2, $roles);
        $this->assertInstanceOf('Symfony\Component\Security\Core\Role\Role', $roles[0]);
        $this->assertSame('ROLE_FOO', $roles[0]->getRole());
        $this->assertInstanceOf('Symfony\Component\Security\Core\Role\Role', $roles[1]);
        $this->assertSame('ROLE_BAR', $roles[1]->getRole());
    }

    public function testAuthenticateWithNullScope()
    {
        $token = new OAuthToken();
        $token->setToken('x');

        $accessToken = new AccessToken();
        $accessToken->setScope(null);

        $this->serverService->expects($this->once())
            ->method('verifyAccessToken')
            ->with('x')
            ->will($this->returnValue($accessToken));

        $result = $this->provider->authenticate($token);

        $this->assertNull($result->getUser());
        $this->assertTrue($result->isAuthenticated());

        $roles = $result->getRoles();
        $this->assertCount(0, $roles);
    }

    public function testAuthenticateWithEmptyScope()
    {
        $token = new OAuthToken();
        $token->setToken('x');

        $accessToken = new AccessToken();
        $accessToken->setScope('');

        $this->serverService->expects($this->once())
            ->method('verifyAccessToken')
            ->with('x')
            ->will($this->returnValue($accessToken));

        $result = $this->provider->authenticate($token);

        $this->assertNull($result->getUser());
        $this->assertTrue($result->isAuthenticated());

        $roles = $result->getRoles();
        $this->assertCount(0, $roles);
    }
}
