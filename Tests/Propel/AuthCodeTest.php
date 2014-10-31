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

class AuthCodeTest extends PropelTestCase
{
    /**
     * @dataProvider getTestHasExpiredData
     */
    public function testHasExpired($expiresAt, $expect)
    {
        $token = new AuthCode();
        $token->setExpiresAt($expiresAt);

        $this->assertSame($expect, $token->hasExpired());
    }

    public static function getTestHasExpiredData()
    {
        return array(
            array(time() + 60, false),
            array(time() - 60, true),
            array(null, false),
        );
    }

    public function testExpiresIn()
    {
        $token = new AuthCode();

        $this->assertEquals(PHP_INT_MAX, $token->getExpiresIn());
    }

    public function testExpiresInWithExpiresAt()
    {
        $token = new AuthCode();
        $token->setExpiresAt(time() + 60);

        $this->assertEquals(60, $token->getExpiresIn());
    }
}
