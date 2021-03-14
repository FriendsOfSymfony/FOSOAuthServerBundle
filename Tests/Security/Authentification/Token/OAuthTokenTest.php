<?php

namespace FOS\OAuthServerBundle\Tests\Security\Authentification\Token;

use FOS\OAuthServerBundle\Model\TokenInterface;
use FOS\OAuthServerBundle\Security\Authentication\Token\OAuthToken;
use PHPUnit\Framework\TestCase;

class OAuthTokenTest extends TestCase
{
    /**
     * @var OAuthToken
     */
    protected $instance;

    public function setUp(): void
    {
        $this->instance = new OAuthToken();

        parent::setUp();
    }

    public function testSetTokenWillSetToken(): void
    {
        $token = $this->getMockBuilder(TokenInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->instance->setToken($token);
    }

    public function testGetTokenWillReturnToken(): void
    {
        $token = $this->getMockBuilder(TokenInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        self::assertNull($this->instance->getToken());
        $this->instance->setToken($token);
        self::assertSame($token, $this->instance->getToken());
    }

    public function testGetCredentialsWillReturnToken(): void
    {
        $token = $this->getMockBuilder(TokenInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        self::assertNull($this->instance->getCredentials());
        $this->instance->setToken($token);
        self::assertSame($token, $this->instance->getCredentials());
    }
}
