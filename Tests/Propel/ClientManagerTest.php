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
use FOS\OAuthServerBundle\Propel\ClientManager;
use FOS\OAuthServerBundle\Propel\ClientQuery;

class ClientManagerTest extends PropelTestCase
{
    const CLIENT_CLASS = Client::class;

    protected $manager;

    public function setUp(): void
    {
        parent::setUp();

        $this->manager = new ClientManager(self::CLIENT_CLASS);
        ClientQuery::create()->deleteAll();
    }

    public function testConstruct()
    {
        self::assertSame(self::CLIENT_CLASS, $this->manager->getClass());
    }

    public function testCreateClass()
    {
        self::assertInstanceOf(self::CLIENT_CLASS, $this->manager->createClient());
    }

    public function testUpdate()
    {
        $client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $client
            ->expects($this->once())
            ->method('save')
        ;

        $this->manager->updateClient($client);
    }

    public function testDelete()
    {
        /** @var Client $client */
        $client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $client
            ->expects($this->once())
            ->method('delete')
        ;

        $this->manager->deleteClient($client);
    }

    public function testFindClientReturnsNullIfNotFound()
    {
        $client = $this->manager->findClientBy(['id' => '1', 'randomId' => '2345']);

        self::assertNull($client);
    }

    public function testFindClientWithInvalidCriteria()
    {
        $client = $this->manager->findClientBy(['randomId' => '2345']);
        self::assertNull($client);

        $client = $this->manager->findClientBy(['id' => '2345']);
        self::assertNull($client);

        $client = $this->manager->findClientBy(['foo' => '2345']);
        self::assertNull($client);
    }

    public function testFindClient()
    {
        $client = $this->createClient('2345');
        $return = $this->manager->findClientBy(['id' => '1', 'randomId' => '2345']);

        self::assertNotNull($return);
        self::assertSame($client, $return);
    }

    public function testFindClientByPublicId()
    {
        $client = $this->createClient('12345');
        $return = $this->manager->findClientByPublicId('1_12345');

        self::assertNotNull($return);
        self::assertSame($client, $return);
    }

    public function testFindClientByPublicIdReturnsNullIfNotFound()
    {
        $return = $this->manager->findClientByPublicId('1_12345');

        self::assertNull($return);
    }

    public function testFindClientByPublicIdReturnsNullIfInvalidPublicId()
    {
        $return = $this->manager->findClientByPublicId('1');
        self::assertNull($return);

        $return = $this->manager->findClientByPublicId('');
        self::assertNull($return);

        // invalid type
        // $return = $this->manager->findClientByPublicId(null);
        // self::assertNull($return);
    }

    protected function createClient($randomId)
    {
        $client = new Client();
        $client->setRandomId($randomId);
        $client->setRedirectUris(['foo']);
        $client->save();

        return $client;
    }
}
