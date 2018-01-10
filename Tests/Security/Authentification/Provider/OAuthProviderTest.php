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

use FOS\OAuthServerBundle\Security\Authentication\Provider\OAuthProvider;
use FOS\OAuthServerBundle\Security\Authentication\Token\OAuthToken;
use FOS\OAuthServerBundle\Model\AccessToken;
use OAuth2\OAuth2;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class OAuthProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|UserInterface
     */
    protected $user;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|UserProviderInterface
     */
    protected $userProvider;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|OAuthProvider
     */
    protected $provider;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|OAuth2
     */
    protected $serverService;

    /**
     * @var UserCheckerInterface
     */
    protected $userChecker;

    public function setUp()
    {
        $this->user = $this->getMockBuilder(UserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->userProvider = $this->getMockBuilder(UserProviderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->serverService = $this->getMockBuilder(OAuth2::class)
            ->disableOriginalConstructor()
            ->setMethods(array('verifyAccessToken'))
            ->getMock();
        $this->userChecker = $this->getMockBuilder(UserCheckerInterface::class)
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
            ->will($this->returnValue(array('ROLE_USER')));

        $accessToken = new AccessToken();
        $accessToken->setUser($this->user);

        $this->serverService->expects($this->once())
            ->method('verifyAccessToken')
            ->with('x')
            ->will($this->returnValue($accessToken));

        $result = $this->provider->authenticate($token);

        $this->assertSame($this->user, $result->getUser());
        $this->assertEquals($token->getToken(), $result->getToken());
        $this->assertTrue($result->isAuthenticated());
        $this->assertCount(1, $result->getRoles());

        $roles = $result->getRoles();
        $this->assertEquals('ROLE_USER', $roles[0]->getRole());
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
        $this->assertInstanceOf(Role::class, $roles[0]);
        $this->assertEquals('ROLE_FOO', $roles[0]->getRole());
        $this->assertInstanceOf(Role::class, $roles[1]);
        $this->assertEquals('ROLE_BAR', $roles[1]->getRole());
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
