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
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ObjectRepository;
use FOS\OAuthServerBundle\Document\AuthCode;
use FOS\OAuthServerBundle\Entity\AuthCodeManager;
use FOS\OAuthServerBundle\Model\AuthCodeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @group time-sensitive
 *
 * Class AuthCodeManagerTest
 *
 * @author Nikola Petkanski <nikola@petkanski.com>
 */
class AuthCodeManagerTest extends TestCase
{
    protected MockObject|EntityManagerInterface $entityManager;
    protected string $className;
    protected AuthCodeManager $instance;

    public function setUp(): void
    {
        $this->entityManager = $this->getMockBuilder(EntityManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->className = 'TestClassName'.\random_bytes(5);

        $this->instance = new AuthCodeManager($this->entityManager, $this->className);

        parent::setUp();
    }

    public function testGetClassWillReturnClassName(): void
    {
        $this->assertSame($this->className, $this->instance->getClass());
    }

    public function testFindAuthCodeBy(): void
    {
        $repository = $this->getMockBuilder(ObjectRepository::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->entityManager
            ->expects($this->once())
            ->method('getRepository')
            ->with($this->className)
            ->willReturn($repository)
        ;

        $criteria = [
            \random_bytes(10),
        ];
        $randomResult = new AuthCode();

        $repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with($criteria)
            ->willReturn($randomResult)
        ;

        $this->assertSame($randomResult, $this->instance->findAuthCodeBy($criteria));
    }

    public function testUpdateAuthCode(): void
    {
        $authCode = $this->getMockBuilder(AuthCodeInterface::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($authCode)
            ->willReturn(null)
        ;

        $this->entityManager
            ->expects($this->once())
            ->method('flush')
            ->with()
            ->willReturn(null)
        ;

        $this->assertNull($this->instance->updateAuthCode($authCode));
    }

    public function testDeleteAuthCode(): void
    {
        $authCode = $this->getMockBuilder(AuthCodeInterface::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->entityManager
            ->expects($this->once())
            ->method('remove')
            ->with($authCode)
            ->willReturn(null)
        ;

        $this->entityManager
            ->expects($this->once())
            ->method('flush')
            ->with()
            ->willReturn(null)
        ;

        $this->assertNull($this->instance->deleteAuthCode($authCode));
    }

    public function testDeleteExpired(): void
    {
        $randomResult = \random_int(0, 10);

        $queryBuilder = $this->getMockBuilder(QueryBuilder::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $repository = $this->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->entityManager
            ->expects($this->once())
            ->method('getRepository')
            ->with($this->className)
            ->willReturn($repository)
        ;

        $repository
            ->expects($this->once())
            ->method('createQueryBuilder')
            ->with('a')
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
            ->with('a.expiresAt < ?1')
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

        $this->assertSame($randomResult, $this->instance->deleteExpired());
    }
}