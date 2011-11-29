<?php

namespace Alb\OAuth2ServerBundle\Tests\Model;

use Alb\OAuth2ServerBundle\Model\OAuth2Token;

class OAuth2TokenTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getTestHasExpiredData
     */
    public function testHasExpired($expiresAt, $expect)
    {
        $token = new OAuth2Token;
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

