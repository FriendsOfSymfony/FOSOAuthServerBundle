<?php

namespace FOS\OAuthServerBundle\Tests\Security\Authentification\Token;

use FOS\OAuthServerBundle\Model\TokenInterface;
use FOS\OAuthServerBundle\Security\Authentication\Token\OAuthToken;

class OAuthTokenTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OAuthToken
     */
    protected $instance;

    public function setUp()
    {
        $this->instance = new OAuthToken();

        parent::setUp();
    }

    public function testSetTokenWillSetToken()
    {
        $token = $this->getMockBuilder(TokenInterface::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->assertNull($this->instance->setToken($token));
        $this->assertAttributeSame($token, 'token', $this->instance);
    }

    public function testGetTokenWillReturnToken()
    {
        $token = $this->getMockBuilder(TokenInterface::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->assertNull($this->instance->getToken());
        $this->assertNull($this->instance->setToken($token));
        $this->assertSame($token, $this->instance->getToken());
    }

    public function testGetCredentialsWillReturnToken()
    {
        $token = $this->getMockBuilder(TokenInterface::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->assertNull($this->instance->getCredentials());
        $this->assertNull($this->instance->setToken($token));
        $this->assertSame($token, $this->instance->getCredentials());
    }
}
