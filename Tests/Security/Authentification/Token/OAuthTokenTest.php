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

class OAuthTokenTest extends \PHPUnit\Framework\TestCase
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
