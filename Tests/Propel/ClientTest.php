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

use FOS\OAuthServerBundle\Propel\Client;
use OAuth2\OAuth2;

class ClientTest extends PropelTestCase
{
    public function testConstructor()
    {
        $client = new Client();

        $this->assertNotNull($client->getRandomId());
        $this->assertNotNull($client->getSecret());

        $types = $client->getAllowedGrantTypes();
        $this->assertCount(1, $types);
        $this->assertEquals(OAuth2::GRANT_TYPE_AUTH_CODE, $types[0]);
    }

    public function testCheckSecretWithInvalidArgument()
    {
        $client = new Client();

        $this->assertFalse($client->checkSecret('foo'));
        $this->assertFalse($client->checkSecret(''));
        $this->assertFalse($client->checkSecret(null));
    }

    public function testCheckSecret()
    {
        $client = new Client();
        $client->setSecret('foo');

        $this->assertTrue($client->checkSecret('foo'));
    }
}
