<?php

namespace Alb\OAuth2ServerBundle\Tests\Security\Authentification\Provider;

use Alb\OAuth2ServerBundle\Security\Authentification\Provider\OAuth2Provider;
use Alb\OAuth2ServerBundle\Security\Authentification\Token\OAuth2Token;
use Alb\OAuth2ServerBundle\Model\OAuth2AccessToken;

class OAuth2ProviderTest extends \PHPUnit_Framework_TestCase
{
    protected $userProvider;

    protected $serverService;

    public function setUp()
    {
        $this->user = $this->getMock('Symfony\Component\Security\Core\User\UserInterface');
        $this->userProvider = $this->getMock('Symfony\Component\Security\Core\User\UserProviderInterface');
        $this->serverService = $this->getMock('OAuth2\OAuth2', array(), array(), '', false);
        $this->provider = new OAuth2Provider($this->userProvider, $this->serverService);
    }

    public function testAuthenticateReturnsTokenIfValid()
    {
        $token = new OAuth2Token;
        $token->setToken('x');

        $accessToken = new OAuth2AccessToken;
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
        $token = new OAuth2Token;
        $token->setToken('x');

        $accessToken = new OAuth2AccessToken;
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

