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

use FOS\OAuthServerBundle\Propel\Client;
use OAuth2\OAuth2;

class ClientTest extends PropelTestCase
{
    public function testConstructor()
    {
        $client = new Client();

        self::assertNotNull($client->getRandomId());
        self::assertNotNull($client->getSecret());

        $types = $client->getAllowedGrantTypes();
        self::assertCount(1, $types);
        self::assertSame(OAuth2::GRANT_TYPE_AUTH_CODE, $types[0]);
    }

    public function testCheckSecretWithInvalidArgument(): void
    {
        $client = new Client();

        self::assertFalse($client->checkSecret('foo'));
        self::assertFalse($client->checkSecret(''));
        self::assertFalse($client->checkSecret(null));
    }

    public function testCheckSecret(): void
    {
        $client = new Client();
        $client->setSecret('foo');

        self::assertTrue($client->checkSecret('foo'));
    }
}
