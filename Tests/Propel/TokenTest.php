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

use FOS\OAuthServerBundle\Propel\Token as AbstractToken;

/**
 * @group time-sensitive
 *
 * Class TokenTest
 */
class TokenTest extends PropelTestCase
{
    /**
     * @dataProvider getTestHasExpiredData
     *
     * @param mixed $expiresAt
     * @param mixed $expect
     */
    public function testHasExpired($expiresAt, $expect): void
    {
        $token = new Token();
        $token->setExpiresAt($expiresAt);

        self::assertSame($expect, $token->hasExpired());
    }

    public static function getTestHasExpiredData(): array
    {
        return [
            [time() + 60, false],
            [time() - 60, true],
            [null, false],
        ];
    }

    public function testExpiresIn(): void
    {
        $token = new Token();

        self::assertSame(PHP_INT_MAX, $token->getExpiresIn());
    }

    public function testExpiresInWithExpiresAt(): void
    {
        $token = new Token();
        $token->setExpiresAt(time() + 60);

        self::assertSame(60, $token->getExpiresIn());
    }
}

// The Token class is abstract (concrete inheritance)
class Token extends AbstractToken
{
}
