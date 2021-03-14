<?php

namespace FOS\OAuthServerBundle\Tests\Entity;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FOS\OAuthServerBundle\Entity\ClientManager;
use FOS\OAuthServerBundle\Model\ClientInterface;
use PHPUnit\Framework\TestCase;

/**
 * Class ClientManagerTest
 * @package FOS\OAuthServerBundle\Tests\Entity
 * @author Nikola Petkanski <nikola@petkanski.com>
 */
class ClientManagerTest extends TestCase
{
    /**
     * @var \\PHPUnit\Framework\MockObject\MockObject|EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var string
     */
    protected $className;

    /**
     * @var \\PHPUnit\Framework\MockObject\MockObject|EntityRepository
     */
    protected $repository;

    /**
     * @var ClientManager
     */
    protected $instance;

    public function setUp(): void
    {
        $this->entityManager = $this->getMockBuilder(EntityManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->repository = $this->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->className = 'RandomClassName'.\random_bytes(5);

        $this->entityManager
            ->expects(self::once())
            ->method('getRepository')
            ->with($this->className)
            ->willReturn($this->repository);

        $this->instance = new ClientManager($this->entityManager, $this->className);

        parent::setUp();
    }

    public function testGetClass(): void
    {
        self::assertSame($this->className, $this->instance->getClass());
    }

    public function testFindClientBy(): void
    {
        $criteria = [
            \random_bytes(5),
        ];
        $randomResult = \random_bytes(5);

        $this->repository
            ->expects(self::once())
            ->method('findOneBy')
            ->with($criteria)
            ->willReturn($randomResult);

        self::assertSame($randomResult, $this->instance->findClientBy($criteria));
    }

    public function testUpdateClient(): void
    {
        $client = $this->getMockBuilder(ClientInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->entityManager
            ->expects(self::once())
            ->method('persist')
            ->with($client)
            ->willReturn(null);

        $this->entityManager
            ->expects(self::once())
            ->method('flush')
            ->with()
            ->willReturn(null);

        $this->instance->updateClient($client);
    }

    public function testDeleteClient(): void
    {
        $client = $this->getMockBuilder(ClientInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->entityManager
            ->expects(self::once())
            ->method('remove')
            ->with($client)
            ->willReturn(null);

        $this->entityManager
            ->expects(self::once())
            ->method('flush')
            ->with()
            ->willReturn(null);

        $this->instance->deleteClient($client);
    }
}
