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

namespace FOS\OAuthServerBundle\Tests\Document;

use Doctrine\MongoDB\Query\Builder;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\DocumentRepository;
use Doctrine\ORM\AbstractQuery;
use FOS\OAuthServerBundle\Document\AuthCodeManager;
use FOS\OAuthServerBundle\Model\AuthCodeInterface;

/**
 * @group time-sensitive
 *
 * Class AuthCodeManagerTest
 *
 * @author Nikola Petkanski <nikola@petkanski.com>
 */
class AuthCodeManagerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|DocumentManager
     */
    protected $documentManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|DocumentRepository
     */
    protected $repository;

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
        if (!class_exists('\Doctrine\ODM\MongoDB\DocumentManager')) {
            $this->markTestSkipped('Doctrine MongoDB ODM has to be installed for this test to run.');
        }

        $this->documentManager = $this->getMockBuilder(DocumentManager::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->repository = $this->getMockBuilder(DocumentRepository::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->className = 'TestClassName'.\random_bytes(5);

        $this->documentManager
            ->expects($this->once())
            ->method('getRepository')
            ->with($this->className)
            ->willReturn($this->repository)
        ;

        $this->instance = new AuthCodeManager($this->documentManager, $this->className);

        parent::setUp();
    }

    public function testConstructWillSetParameters()
    {
        $this->assertAttributeSame($this->documentManager, 'dm', $this->instance);
        $this->assertAttributeSame($this->className, 'class', $this->instance);
    }

    public function testGetClassWillReturnClassName()
    {
        $this->assertSame($this->className, $this->instance->getClass());
    }

    public function testFindAuthCodeBy()
    {
        $randomResult = \random_bytes(10);
        $criteria = [
            \random_bytes(10),
        ];

        $this->repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with($criteria)
            ->willReturn($randomResult)
        ;

        $this->assertSame($randomResult, $this->instance->findAuthCodeBy($criteria));
    }

    public function testUpdateAuthCode()
    {
        $authCode = $this->getMockBuilder(AuthCodeInterface::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->documentManager
            ->expects($this->once())
            ->method('persist')
            ->with($authCode)
            ->willReturn(null)
        ;

        $this->documentManager
            ->expects($this->once())
            ->method('flush')
            ->with()
            ->willReturn(null)
        ;

        $this->assertNull($this->instance->updateAuthCode($authCode));
    }

    public function testDeleteAuthCode()
    {
        $authCode = $this->getMockBuilder(AuthCodeInterface::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->documentManager
            ->expects($this->once())
            ->method('remove')
            ->with($authCode)
            ->willReturn(null)
        ;

        $this->documentManager
            ->expects($this->once())
            ->method('flush')
            ->with()
            ->willReturn(null)
        ;

        $this->assertNull($this->instance->deleteAuthCode($authCode));
    }

    public function testDeleteExpired()
    {
        $queryBuilder = $this->getMockBuilder(Builder::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->repository
            ->expects($this->once())
            ->method('createQueryBuilder')
            ->with()
            ->willReturn($queryBuilder)
        ;

        $queryBuilder
            ->expects($this->once())
            ->method('remove')
            ->with()
            ->willReturn($queryBuilder)
        ;

        $queryBuilder
            ->expects($this->once())
            ->method('field')
            ->with('expiresAt')
            ->willReturn($queryBuilder)
        ;

        $queryBuilder
            ->expects($this->once())
            ->method('lt')
            ->with(time())
            ->willReturn($queryBuilder)
        ;

        $query = $this->getMockBuilder(AbstractQuery::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $queryBuilder
            ->expects($this->once())
            ->method('getQuery')
            ->with([
                'safe' => true,
            ])
            ->willReturn($query)
        ;

        $data = [
            'n' => \random_bytes(10),
        ];

        $query
            ->expects($this->once())
            ->method('execute')
            ->with()
            ->willReturn($data)
        ;

        $this->assertSame($data['n'], $this->instance->deleteExpired());
    }
}
