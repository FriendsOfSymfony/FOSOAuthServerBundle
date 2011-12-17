<?php

/*
 * This file is part of the FOSOAuthServerBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\OAuthServerBundle\Tests\Model;

use FOS\OAuthServerBundle\Model\Token;

class TokenTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getTestHasExpiredData
     */
    public function testHasExpired($expiresAt, $expect)
    {
        $token = new Token;
        $token->setExpiresAt($expiresAt);

        $this->assertSame($expect, $token->hasExpired());
    }

    public function getTestHasExpiredData()
    {
        return array(
            array(time() + 60, false),
            array(time() - 60, true),
            array(null, false),
        );
    }
}

