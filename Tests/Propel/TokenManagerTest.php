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

namespace FOS\OAuthServerBundle\Tests\Propel;

use FOS\OAuthServerBundle\Propel\RefreshToken as Token;
use FOS\OAuthServerBundle\Propel\RefreshTokenQuery as TokenQuery;
use FOS\OAuthServerBundle\Propel\TokenManager;

/**
 * @group time-sensitive
 *
 * Class TokenManagerTest
 */
class TokenManagerTest extends PropelTestCase
{
    const TOKEN_CLASS = 'FOS\OAuthServerBundle\Propel\RefreshToken';

    protected $manager;

    public function setUp(): void
    {
        parent::setUp();

        $this->manager = new TokenManager(self::TOKEN_CLASS);
        TokenQuery::create()->deleteAll();
    }

    public function testConstruct(): void
    {
        $this->assertSame(self::TOKEN_CLASS, $this->manager->getClass());
    }

    public function testCreateClass(): void
    {
        $this->manager = new TokenManager('Token');
        $this->assertInstanceOf('Token', $this->manager->createToken());
    }

    public function testUpdate(): void
    {
        $token = $this->getMockBuilder('FOS\OAuthServerBundle\Propel\Token')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $token
            ->expects($this->once())
            ->method('save')
        ;

        $this->manager->updateToken($token);
    }

    public function testDelete(): void
    {
        $token = $this->getMockBuilder('FOS\OAuthServerBundle\Propel\Token')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $token
            ->expects($this->once())
            ->method('delete')
        ;

        $this->manager->deleteToken($token);
    }

    public function testFindTokenReturnsNullIfNotFound(): void
    {
        $token = $this->manager->findTokenBy(['token' => '12345']);

        $this->assertNull($token);
    }

    public function testFindTokenWithInvalidData(): void
    {
        $token = $this->manager->findTokenBy(['foo' => '12345']);
        $this->assertNull($token);

        $token = $this->manager->findTokenBy([]);
        $this->assertNull($token);

        $token = $this->manager->findTokenBy(['token']);
        $this->assertNull($token);
    }

    public function testFindToken(): void
    {
        $token = $this->createToken('12345');
        $return = $this->manager->findTokenBy(['token' => '12345']);

        $this->assertNotNull($return);
        $this->assertSame($token, $return);
    }

    public function testFindTokenByToken(): void
    {
        $token = $this->createToken('12345');
        $return = $this->manager->findTokenByToken('12345');

        $this->assertNotNull($return);
        $this->assertSame($token, $return);
    }

    public function testFindTokenByTokenReturnsNullIfNotFound(): void
    {
        $return = $this->manager->findTokenByToken('12345');

        $this->assertNull($return);
    }

    public function testDeleteExpired(): void
    {
        $a1 = $this->createToken('12345', time() + 100);
        $a2 = $this->createToken('67890', time() - 100);

        $this->assertSame(2, TokenQuery::create()->count());

        $nb = $this->manager->deleteExpired();

        $this->assertSame(1, $nb);
        $this->assertTrue($a1->equals(TokenQuery::create()->findOne()));
    }

    protected function createToken($tokenString, $expiresAt = false)
    {
        $token = new Token();
        $token->setClientId(1);
        $token->setToken($tokenString);

        if ($expiresAt) {
            $token->setExpiresAt($expiresAt);
        }

        $token->save();

        return $token;
    }
}
