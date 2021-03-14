<?php

namespace FOS\OAuthServerBundle\Tests\Entity;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ObjectRepository;
use FOS\OAuthServerBundle\Entity\AuthCodeManager;
use FOS\OAuthServerBundle\Model\AuthCodeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @group time-sensitive
 *
 * Class AuthCodeManagerTest
 * @package FOS\OAuthServerBundle\Tests\Entity
 * @author Nikola Petkanski <nikola@petkanski.com>
 */
class AuthCodeManagerTest extends TestCase
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
     * @var AuthCodeManager
     */
    protected $instance;

    public function setUp(): void
    {
        $this->entityManager = $this->getMockBuilder(EntityManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->className = 'TestClassName'.random_bytes(5);

        $this->instance = new AuthCodeManager($this->entityManager, $this->className);

        parent::setUp();
    }

    public function testGetClassWillReturnClassName(): void
    {
        self::assertSame($this->className, $this->instance->getClass());
    }

    public function testFindAuthCodeBy(): void
    {
        $repository = $this->getMockBuilder(ObjectRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->entityManager
            ->expects(self::once())
            ->method('getRepository')
            ->with($this->className)
            ->willReturn($repository);

        $criteria = [random_bytes(10)];
        $randomResult = random_bytes(10);

        $repository
            ->expects(self::once())
            ->method('findOneBy')
            ->with($criteria)
            ->willReturn($randomResult);

        self::assertSame($randomResult, $this->instance->findAuthCodeBy($criteria));
    }

    public function testUpdateAuthCode(): void
    {
        $authCode = $this->getMockBuilder(AuthCodeInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->entityManager
            ->expects(self::once())
            ->method('persist')
            ->with($authCode)
            ->willReturn(null);

        $this->entityManager
            ->expects(self::once())
            ->method('flush')
            ->with()
            ->willReturn(null);

        $this->instance->updateAuthCode($authCode);
    }

    public function testDeleteAuthCode(): void
    {
        $authCode = $this->getMockBuilder(AuthCodeInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->entityManager
            ->expects(self::once())
            ->method('remove')
            ->with($authCode)
            ->willReturn(null);

        $this->entityManager
            ->expects(self::once())
            ->method('flush')
            ->with()
            ->willReturn(null);

        $this->instance->deleteAuthCode($authCode);
    }

    public function testDeleteExpired(): void
    {
        $randomResult = random_bytes(10);

        $queryBuilder = $this->getMockBuilder(QueryBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $repository = $this->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->entityManager
            ->expects(self::once())
            ->method('getRepository')
            ->with($this->className)
            ->willReturn($repository);

        $repository
            ->expects(self::once())
            ->method('createQueryBuilder')
            ->with('a')
            ->willReturn($queryBuilder);

        $queryBuilder
            ->expects(self::once())
            ->method('delete')
            ->with()
            ->willReturn($queryBuilder);

        $queryBuilder
            ->expects(self::once())
            ->method('where')
            ->with('a.expiresAt < ?1')
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
