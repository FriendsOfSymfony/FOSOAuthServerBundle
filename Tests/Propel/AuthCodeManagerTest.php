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

use FOS\OAuthServerBundle\Propel\AuthCode;
use FOS\OAuthServerBundle\Propel\AuthCodeQuery;
use FOS\OAuthServerBundle\Propel\AuthCodeManager;

class AuthCodeManagerTest extends PropelTestCase
{
    const AUTH_CODE_CLASS = 'FOS\OAuthServerBundle\Propel\AuthCode';

    protected $manager;

    public function setUp()
    {
        parent::setUp();

        $this->manager = new AuthCodeManager(self::AUTH_CODE_CLASS);
        AuthCodeQuery::create()->deleteAll();
    }

    public function testConstruct()
    {
        $this->assertEquals(self::AUTH_CODE_CLASS, $this->manager->getClass());
    }

    public function testCreateClass()
    {
        $this->assertInstanceOf(self::AUTH_CODE_CLASS, $this->manager->createAuthCode());
    }

    public function testUpdate()
    {
        $authCode = $this->getMock('FOS\OAuthServerBundle\Propel\AuthCode');
        $authCode
            ->expects($this->once())
            ->method('save');

        $this->manager->updateAuthCode($authCode);
    }

    public function testDelete()
    {
        $authCode = $this->getMock('FOS\OAuthServerBundle\Propel\AuthCode');
        $authCode
            ->expects($this->once())
            ->method('delete');

        $this->manager->deleteAuthCode($authCode);
    }

    public function testFindAuthCodeReturnsNullIfNotFound()
    {
        $authCode = $this->manager->findAuthCodeBy(array('token' => '12345'));

        $this->assertNull($authCode);
    }

    public function testFindAuthCode()
    {
        $authCode = $this->createAuthCode('12345');
        $return   = $this->manager->findAuthCodeBy(array('token' => '12345'));

        $this->assertNotNull($return);
        $this->assertSame($authCode, $return);
    }

    public function testFindAuthCodeByToken()
    {
        $authCode = $this->createAuthCode('12345');
        $return   = $this->manager->findAuthCodeByToken('12345');

        $this->assertNotNull($return);
        $this->assertSame($authCode, $return);
    }

    public function testFindAuthCodeByTokenReturnsNullIfNotFound()
    {
        $return = $this->manager->findAuthCodeByToken('12345');

        $this->assertNull($return);
    }

    public function testFindAuthCodeWithInvalidData()
    {
        $token = $this->manager->findAuthCodeBy(array('foo' => '12345'));
        $this->assertNull($token);

        $token = $this->manager->findAuthCodeBy(array());
        $this->assertNull($token);

        $token = $this->manager->findAuthCodeBy(array('token'));
        $this->assertNull($token);
    }

    public function testDeleteExpired()
    {
        $a1 = $this->createAuthCode('12345', time() + 100);
        $a2 = $this->createAuthCode('67890', time() - 100);

        $this->assertEquals(2, AuthCodeQuery::create()->count());

        $nb = $this->manager->deleteExpired();

        $this->assertEquals(1, $nb);
        $this->assertTrue($a1->equals(AuthCodeQuery::create()->findOne()));
    }

    protected function createAuthCode($token, $expiresAt = false)
    {
        $authCode = new AuthCode();
        $authCode->setClientId(1);
        $authCode->setToken($token);
        $authCode->setRedirectUri('foo');

        if ($expiresAt) {
            $authCode->setExpiresAt($expiresAt);
        }

        $authCode->save();

        return $authCode;
    }
}
