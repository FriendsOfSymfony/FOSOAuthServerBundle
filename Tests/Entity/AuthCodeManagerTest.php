<?php

namespace FOS\OAuthServerBundle\Tests\Entity;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use FOS\OAuthServerBundle\Entity\AuthCodeManager;
use FOS\OAuthServerBundle\Model\AuthCodeInterface;

/**
 * @group time-sensitive
 *
 * Class AuthCodeManagerTest
 * @package FOS\OAuthServerBundle\Tests\Entity
 * @author Nikola Petkanski <nikola@petkanski.com>
 */
class AuthCodeManagerTest extends \PHPUnit_Framework_TestCase
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
     * @var AuthCodeManager
     */
    protected $instance;

    public function setUp()
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->className = 'TestClassName' . \random_bytes(5);

        $this->instance = new AuthCodeManager($this->entityManager, $this->className);

        parent::setUp();
    }

    public function testConstructWillSetParameters()
    {
        $this->assertAttributeSame($this->entityManager, 'em', $this->instance);
        $this->assertAttributeSame($this->className, 'class', $this->instance);
    }

    public function testGetClassWillReturnClassName()
    {
        $this->assertSame($this->className, $this->instance->getClass());
    }

    public function testFindAuthCodeBy()
    {
        $repository = $this->createMock(ObjectRepository::class);

        $this->entityManager
            ->expects($this->once())
            ->method('getRepository')
            ->with($this->className)
            ->willReturn($repository)
        ;

        $criteria = [
            \random_bytes(10)
        ];
        $randomResult = \random_bytes(10);

        $repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with($criteria)
            ->willReturn($randomResult)
        ;

        $this->assertSame($randomResult, $this->instance->findAuthCodeBy($criteria));
    }

    public function testUpdateAuthCode()
    {
        $authCode = $this->createMock(AuthCodeInterface::class);

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

    public function testDeleteAuthCode()
    {
        $authCode = $this->createMock(AuthCodeInterface::class);

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

    public function testDeleteExpired()
    {
        $randomResult = \random_bytes(10);

        $queryBuilder = $this->createMock(QueryBuilder::class);

        $repository = $this->createMock(EntityRepository::class);

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

        $query = $this->createMock(AbstractQuery::class);

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