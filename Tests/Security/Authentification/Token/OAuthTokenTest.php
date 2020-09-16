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
            ->getMock()
        ;

        self::assertNull($this->instance->setToken($token));
        self::assertSame($token, $this->instance->getToken());
    }

    public function testGetTokenWillReturnToken(): void
    {
        $token = $this->getMockBuilder(TokenInterface::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        self::assertNull($this->instance->getToken());
        self::assertNull($this->instance->setToken($token));
        self::assertSame($token, $this->instance->getToken());
    }

    public function testGetCredentialsWillReturnToken(): void
    {
        $token = $this->getMockBuilder(TokenInterface::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        self::assertNull($this->instance->getCredentials());
        self::assertNull($this->instance->setToken($token));
        self::assertSame($token, $this->instance->getCredentials());
    }
}
