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

namespace FOS\OAuthServerBundle\Tests\Entity;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FOS\OAuthServerBundle\Entity\ClientManager;
use FOS\OAuthServerBundle\Model\ClientInterface;
use FOS\OAuthServerBundle\Tests\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use function random_bytes;

/**
 * Class ClientManagerTest.
 *
 * @author Nikola Petkanski <nikola@petkanski.com>
 */
class ClientManagerTest extends TestCase
{
    /**
     * @var MockObject|EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var string
     */
    protected $className;

    /**
     * @var MockObject|EntityRepository
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
            ->getMock()
        ;
        $this->repository = $this->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->className = 'RandomClassName'.random_bytes(5);

        $this->entityManager
            ->expects($this->once())
            ->method('getRepository')
            ->with($this->className)
            ->willReturn($this->repository)
        ;

        $this->instance = new ClientManager($this->entityManager, $this->className);

        parent::setUp();
    }

    public function testConstructWillSetParameters(): void
    {
        self::assertObjectPropertySame($this->entityManager, $this->instance, 'em');
        self::assertObjectPropertySame($this->repository, $this->instance, 'repository');
        self::assertSame($this->className, $this->instance->getClass());
    }

    public function testGetClass(): void
    {
        self::assertSame($this->className, $this->instance->getClass());
    }

    public function testFindClientBy(): void
    {
        $criteria = [
            random_bytes(5),
        ];
        $randomResult = random_bytes(5);

        $this->repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with($criteria)
            ->willReturn($randomResult)
        ;

        self::assertSame($randomResult, $this->instance->findClientBy($criteria));
    }

    public function testUpdateClient(): void
    {
        $client = $this->getMockBuilder(ClientInterface::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

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

        self::assertNull($this->instance->updateClient($client));
    }

    public function testDeleteClient(): void
    {
        $client = $this->getMockBuilder(ClientInterface::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

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

        self::assertNull($this->instance->deleteClient($client));
    }
}
