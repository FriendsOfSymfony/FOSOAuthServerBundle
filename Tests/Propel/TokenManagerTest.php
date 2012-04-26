<?php

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

class TokenManagerTest extends PropelTestCase
{
    const TOKEN_CLASS = 'FOS\OAuthServerBundle\Propel\RefreshToken';

    protected $manager;

    public function setUp()
    {
        parent::setUp();

        $this->manager = new TokenManager(self::TOKEN_CLASS);
        TokenQuery::create()->deleteAll();
    }

    public function testConstruct()
    {
        $this->assertEquals(self::TOKEN_CLASS, $this->manager->getClass());
    }

    public function testCreateClass()
    {
        $this->manager = new TokenManager('Token');
        $this->assertInstanceOf('Token', $this->manager->createToken());
    }

    public function testUpdate()
    {
        $token = $this->getMock('FOS\OAuthServerBundle\Propel\Token');
        $token
            ->expects($this->once())
            ->method('save');

        $this->manager->updateToken($token);
    }

    public function testDelete()
    {
        $token = $this->getMock('FOS\OAuthServerBundle\Propel\Token');
        $token
            ->expects($this->once())
            ->method('delete');

        $this->manager->deleteToken($token);
    }

    public function testFindTokenReturnsNullIfNotFound()
    {
        $token = $this->manager->findTokenBy(array('token' => '12345'));

        $this->assertNull($token);
    }

    public function testFindTokenWithInvalidData()
    {
        $token = $this->manager->findTokenBy(array('foo' => '12345'));
        $this->assertNull($token);

        $token = $this->manager->findTokenBy(array());
        $this->assertNull($token);

        $token = $this->manager->findTokenBy(array('token'));
        $this->assertNull($token);
    }

    public function testFindToken()
    {
        $token  = $this->createToken('12345');
        $return = $this->manager->findTokenBy(array('token' => '12345'));

        $this->assertNotNull($return);
        $this->assertSame($token, $return);
    }

    public function testFindTokenByToken()
    {
        $token  = $this->createToken('12345');
        $return = $this->manager->findTokenByToken('12345');

        $this->assertNotNull($return);
        $this->assertSame($token, $return);
    }

    public function testFindTokenByTokenReturnsNullIfNotFound()
    {
        $return = $this->manager->findTokenByToken('12345');

        $this->assertNull($return);
    }

    public function testDeleteExpired()
    {
        $a1 = $this->createToken('12345', time() + 100);
        $a2 = $this->createToken('67890', time() - 100);

        $this->assertEquals(2, TokenQuery::create()->count());

        $nb = $this->manager->deleteExpired();

        $this->assertEquals(1, $nb);
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
