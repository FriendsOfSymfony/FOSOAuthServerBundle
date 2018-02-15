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
    const CLIENT_CLASS = 'FOS\OAuthServerBundle\Propel\Client';

    protected $manager;

    public function setUp()
    {
        parent::setUp();

        $this->manager = new ClientManager(self::CLIENT_CLASS);
        ClientQuery::create()->deleteAll();
    }

    public function testConstruct()
    {
        $this->assertSame(self::CLIENT_CLASS, $this->manager->getClass());
    }

    public function testCreateClass()
    {
        $this->assertInstanceOf(self::CLIENT_CLASS, $this->manager->createClient());
    }

    public function testUpdate()
    {
        $client = $this->getMockBuilder('FOS\OAuthServerBundle\Propel\Client')
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
        $client = $this->getMockBuilder('FOS\OAuthServerBundle\Propel\Client')
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

        $this->assertNull($client);
    }

    public function testFindClientWithInvalidCriteria()
    {
        $client = $this->manager->findClientBy(['randomId' => '2345']);
        $this->assertNull($client);

        $client = $this->manager->findClientBy(['id' => '2345']);
        $this->assertNull($client);

        $client = $this->manager->findClientBy(['foo' => '2345']);
        $this->assertNull($client);
    }

    public function testFindClient()
    {
        $client = $this->createClient('2345');
        $return = $this->manager->findClientBy(['id' => '1', 'randomId' => '2345']);

        $this->assertNotNull($return);
        $this->assertSame($client, $return);
    }

    public function testFindClientByPublicId()
    {
        $client = $this->createClient('12345');
        $return = $this->manager->findClientByPublicId('1_12345');

        $this->assertNotNull($return);
        $this->assertSame($client, $return);
    }

    public function testFindClientByPublicIdReturnsNullIfNotFound()
    {
        $return = $this->manager->findClientByPublicId('1_12345');

        $this->assertNull($return);
    }

    public function testFindClientByPublicIdReturnsNullIfInvalidPublicId()
    {
        $return = $this->manager->findClientByPublicId('1');
        $this->assertNull($return);

        $return = $this->manager->findClientByPublicId('');
        $this->assertNull($return);

        // invalid type
        // $return = $this->manager->findClientByPublicId(null);
        // $this->assertNull($return);
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
