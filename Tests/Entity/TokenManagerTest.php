<?php

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
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use FOS\OAuthServerBundle\Entity\AccessToken;
use FOS\OAuthServerBundle\Entity\TokenManager;
use FOS\OAuthServerBundle\Model\TokenInterface;
use PHPUnit\Framework\TestCase;

/**
 * @group time-sensitive
 *
 * Class TokenManagerTest
 * @package FOS\OAuthServerBundle\Tests\Entity
 * @author Nikola Petkanski <nikola@petkanski.com>
 */
class TokenManagerTest extends TestCase
{
    /**
     * @var \\PHPUnit\Framework\MockObject\MockObject|EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var \\PHPUnit\Framework\MockObject\MockObject|EntityRepository
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
            ->getMock();
        $this->entityManager = $this->getMockBuilder(EntityManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->entityManager
            ->expects(self::once())
            ->method('getRepository')
            ->with($this->className)
            ->willReturn($this->repository);

        $this->instance = new TokenManager($this->entityManager, $this->className);
    }

    public function testUpdateTokenPersistsAndFlushes(): void
    {
        $token = new AccessToken();

        $this->entityManager
            ->expects(self::once())
            ->method('persist')
            ->with($token);

        $this->entityManager
            ->expects(self::once())
            ->method('flush')
            ->with();

        $this->instance->updateToken($token);
    }

    public function testGetClass(): void
    {
        self::assertSame($this->className, $this->instance->getClass());
    }

    public function testFindTokenBy(): void
    {
        $randomResult = \random_bytes(5);

        $criteria = [
            \random_bytes(5),
        ];

        $this->repository
            ->expects(self::once())
            ->method('findOneBy')
            ->with($criteria)
            ->willReturn($randomResult);

        self::assertSame($randomResult, $this->instance->findTokenBy($criteria));
    }

    public function testUpdateToken(): void
    {
        $token = $this->getMockBuilder(TokenInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->entityManager
            ->expects(self::once())
            ->method('persist')
            ->with($token)
            ->willReturn(null);

        $this->entityManager
            ->expects(self::once())
            ->method('flush')
            ->with()
            ->willReturn(null);

        $this->instance->updateToken($token);
    }

    public function testDeleteToken(): void
    {
        $token = $this->getMockBuilder(TokenInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->entityManager
            ->expects(self::once())
            ->method('remove')
            ->with($token)
            ->willReturn(null);

        $this->entityManager
            ->expects(self::once())
            ->method('flush')
            ->with()
            ->willReturn(null);

        $this->instance->deleteToken($token);
    }

    public function testDeleteExpired(): void
    {
        $randomResult = \random_bytes(10);

        $queryBuilder = $this->getMockBuilder(QueryBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->repository
            ->expects(self::once())
            ->method('createQueryBuilder')
            ->with('t')
            ->willReturn($queryBuilder);

        $queryBuilder
            ->expects(self::once())
            ->method('delete')
            ->with()
            ->willReturn($queryBuilder);

        $queryBuilder
            ->expects(self::once())
            ->method('where')
            ->with('t.expiresAt < ?1')
            ->willReturn($queryBuilder);

        $queryBuilder
            ->expects(self::once())
            ->method('setParameters')
            ->with([1 => time()])
            ->willReturn($queryBuilder);

        $query = $this->getMockBuilder(AbstractQuery::class)
            ->disableOriginalConstructor()
            ->getMock();

        $queryBuilder
            ->expects(self::once())
            ->method('getQuery')
            ->with()
            ->willReturn($query);

        $query
            ->expects(self::once())
            ->method('execute')
            ->with()
            ->willReturn($randomResult);

        self::assertSame($randomResult, $this->instance->deleteExpired());
    }
}
