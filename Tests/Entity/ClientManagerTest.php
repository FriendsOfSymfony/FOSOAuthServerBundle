<?php

namespace FOS\OAuthServerBundle\Tests\Entity;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FOS\OAuthServerBundle\Entity\ClientManager;
use FOS\OAuthServerBundle\Model\ClientInterface;

/**
 * Class ClientManagerTest
 * @package FOS\OAuthServerBundle\Tests\Entity
 * @author Nikola Petkanski <nikola@petkanski.com>
 */
class ClientManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var string
     */
    protected $className;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|EntityRepository
     */
    protected $repository;

    /**
     * @var ClientManager
     */
    protected $instance;

    public function setUp()
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->repository = $this->createMock(EntityRepository::class);
        $this->className = 'RandomClassName'. \random_bytes(5);

        $this->entityManager
            ->expects($this->once())
            ->method('getRepository')
            ->with($this->className)
            ->willReturn($this->repository)
        ;

        $this->instance = new ClientManager($this->entityManager, $this->className);

        parent::setUp();
    }

    public function testConstructWillSetParameters()
    {
        $this->assertAttributeSame($this->entityManager, 'em', $this->instance);
        $this->assertAttributeSame($this->repository, 'repository', $this->instance);
        $this->assertAttributeSame($this->className, 'class', $this->instance);
    }

    public function testGetClass()
    {
        $this->assertSame($this->className, $this->instance->getClass());
    }

    public function testFindClientBy()
    {
        $criteria = [
            \random_bytes(5),
        ];
        $randomResult = \random_bytes(5);

        $this->repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with($criteria)
            ->willReturn($randomResult)
        ;

        $this->assertSame($randomResult, $this->instance->findClientBy($criteria));
    }

    public function testUpdateClient()
    {
        $client = $this->createMock(ClientInterface::class);

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($client)
            ->willReturn(null)
        ;

        $this->entityManager
            ->expects($this->once())
            ->method('flush')
            ->with()
            ->willReturn(null)
        ;

        $this->assertNull($this->instance->updateClient($client));
    }

    public function testDeleteClient()
    {
        $client = $this->createMock(ClientInterface::class);

        $this->entityManager
            ->expects($this->once())
            ->method('remove')
            ->with($client)
            ->willReturn(null)
        ;

        $this->entityManager
            ->expects($this->once())
            ->method('flush')
            ->with()
            ->willReturn(null)
        ;

        $this->assertNull($this->instance->deleteClient($client));
    }
}