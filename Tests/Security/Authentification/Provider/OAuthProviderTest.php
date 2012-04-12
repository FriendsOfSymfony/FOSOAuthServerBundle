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

class OAuthProviderTest extends \PHPUnit_Framework_TestCase
{
    protected $user;
    protected $userProvider;
    protected $provider;
    protected $serverService;

    public function setUp()
    {
        $this->user = $this->getMock('Symfony\Component\Security\Core\User\UserInterface');
        $this->userProvider = $this->getMock('Symfony\Component\Security\Core\User\UserProviderInterface');
        $this->serverService = $this->getMock('OAuth2\OAuth2', array('verifyAccessToken'), array(), '', false);
        $this->provider = new OAuthProvider($this->userProvider, $this->serverService);
    }

    public function testAuthenticateReturnsTokenIfValid()
    {
        $token = new OAuthToken;
        $token->setToken('x');

        $accessToken = new AccessToken();
        $accessToken->setData($this->user);

        $this->serverService->expects($this->once())
            ->method('verifyAccessToken')
            ->with('x')
            ->will($this->returnValue($accessToken));

        $result = $this->provider->authenticate($token);

        $this->assertSame($token, $result);
        $this->assertSame($this->user, $result->getUser());
    }

    public function testAuthenticateReturnsTokenIfValidEvenIfNullData()
    {
        $token = new OAuthToken;
        $token->setToken('x');

        $accessToken = new AccessToken();
        $accessToken->setData(null);

        $this->serverService->expects($this->once())
            ->method('verifyAccessToken')
            ->with('x')
            ->will($this->returnValue($accessToken));

        $result = $this->provider->authenticate($token);

        $this->assertSame($token, $result);
        $this->assertNull($result->getUser());
    }
}

