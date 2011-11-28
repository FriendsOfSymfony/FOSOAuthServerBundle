<?php

namespace Alb\OAuth2ServiceBundle\Tests\Service;

use Alb\OAuth2ServerBundle\Model\OAuth2Client;
use Alb\OAuth2ServerBundle\Service\OAuth2StorageService;

class OAuth2StorageServiceTest extends \PHPUnit_Framework_TestCase
{
    protected $clientManager;
    protected $accessTokenManager;
    protected $storage;

    public function setUp()
    {
        $this->clientManager = $this->getMock('Alb\OAuth2ServerBundle\Model\OAuth2ClientManagerInterface');
        $this->accessTokenManager = $this->getMock('Alb\OAuth2ServerBundle\Model\OAuth2AccessTokenManagerInterface');

        $this->storage = new OAuth2StorageService($this->clientManager, $this->accessTokenManager);
    }

    public function testGetClientReturnsClientWithGivenId()
    {
        $client = new OAuth2Client;

        $this->clientManager->expects($this->once())
            ->method('findClientByPublicId')
            ->with('123_abc')
            ->will($this->returnValue($client));

        $this->assertSame($client, $this->storage->getClient('123_abc'));
    }
}

