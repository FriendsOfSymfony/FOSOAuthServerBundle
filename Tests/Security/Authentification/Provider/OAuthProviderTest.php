<?php

namespace FOS\OAuthServerBundle\Tests\Security\Authentification\Provider;

use FOS\OAuthServerBundle\Security\Authentification\Provider\OAuthProvider;
use FOS\OAuthServerBundle\Security\Authentification\Token\OAuthToken;
use FOS\OAuthServerBundle\Model\AccessToken;

class OAuth2ProviderTest extends \PHPUnit_Framework_TestCase
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

