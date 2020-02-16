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

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use FOS\OAuthServerBundle\Entity\AccessToken;
use FOS\OAuthServerBundle\Entity\TokenManager;
use FOS\OAuthServerBundle\Model\TokenInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use function random_bytes;

/**
 * @group time-sensitive
 *
 * Class TokenManagerTest
 *
 * @author Nikola Petkanski <nikola@petkanski.com>
 */
class TokenManagerTest extends TestCase
{
    /**
     * @var MockObject|EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var MockObject|EntityRepository
     */
    protected $repository;

    /**
     * @var string
     */
    protected $className;

    /**
     * @var TokenManager
     */
    protected $instance;

    public function setUp(): void
    {
        $this->className = AccessToken::class;
        $this->repository = $this->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->entityManager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->entityManager
            ->expects($this->once())
            ->method('getRepository')
            ->with($this->className)
            ->willReturn($this->repository)
        ;

        $this->instance = new TokenManager($this->entityManager, $this->className);
    }

    public function testConstructWillSetParameters(): void
    {
        self::assertSame($this->entityManager, $this->instance->getEntityManager());
        self::assertSame($this->repository, $this->instance->getRepository());
        self::assertSame($this->className, $this->instance->getClass());
    }

    public function testUpdateTokenPersistsAndFlushes(): void
    {
        $token = new AccessToken();

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($token)
        ;

        $this->entityManager
            ->expects($this->once())
            ->method('flush')
            ->with()
        ;

        self::assertNull($this->instance->updateToken($token));
    }

    public function testGetClass(): void
    {
        self::assertSame($this->className, $this->instance->getClass());
    }

    public function testFindTokenBy(): void
    {
        $randomResult = random_bytes(5);

        $criteria = [
            random_bytes(5),
        ];

        $this->repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with($criteria)
            ->willReturn($randomResult)
        ;

        self::assertSame($randomResult, $this->instance->findTokenBy($criteria));
    }

    public function testUpdateToken(): void
    {
        /** @var TokenInterface $token */
        $token = $this->getMockBuilder(TokenInterface::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($token)
            ->willReturn(null)
        ;

        $this->entityManager
            ->expects($this->once())
            ->method('flush')
            ->with()
            ->willReturn(null)
        ;

        self::assertNull($this->instance->updateToken($token));
    }

    public function testDeleteToken(): void
    {
        /** @var TokenInterface $token */
        $token = $this->getMockBuilder(TokenInterface::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->entityManager
            ->expects($this->once())
            ->method('remove')
            ->with($token)
            ->willReturn(null)
        ;

        $this->entityManager
            ->expects($this->once())
            ->method('flush')
            ->with()
            ->willReturn(null)
        ;

        self::assertNull($this->instance->deleteToken($token));
    }

    public function testDeleteExpired(): void
    {
        $randomResult = random_bytes(10);

        $queryBuilder = $this->getMockBuilder(QueryBuilder::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->repository
            ->expects($this->once())
            ->method('createQueryBuilder')
            ->with('t')
            ->willReturn($queryBuilder)
        ;

        $queryBuilder
            ->expects($this->once())
            ->method('delete')
            ->with()
            ->willReturn($queryBuilder)
        ;

        $queryBuilder
            ->expects($this->once())
            ->method('where')
            ->with('t.expiresAt < ?1')
            ->willReturn($queryBuilder)
        ;

        $queryBuilder
            ->expects($this->once())
            ->method('setParameters')
            ->with([1 => time()])
            ->willReturn($queryBuilder)
        ;

        $query = $this->getMockBuilder(AbstractQuery::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $queryBuilder
            ->expects($this->once())
            ->method('getQuery')
            ->with()
            ->willReturn($query)
        ;

        $query
            ->expects($this->once())
            ->method('execute')
            ->with()
            ->willReturn($randomResult)
        ;

        self::assertSame($randomResult, $this->instance->deleteExpired());
    }
}
