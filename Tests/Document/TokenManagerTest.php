<?php

/*
 * This file is part of the FOSOAuthServerBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\OAuthServerBundle\Tests\Document;

use Doctrine\MongoDB\Query\Query;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\DocumentRepository;
use Doctrine\ODM\MongoDB\Query\Builder;
use FOS\OAuthServerBundle\Document\TokenManager;
use FOS\OAuthServerBundle\Document\AccessToken;

/**
 * @group time-sensitive
 *
 * Class TokenManagerTest
 * @package FOS\OAuthServerBundle\Tests\Document
 * @author Nikola Petkanski <nikola@petkanski.com>
 */
class TokenManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $className;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|DocumentManager
     */
    protected $documentManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|DocumentRepository
     */
    protected $repository;

    /**
     * @var TokenManager
     */
    protected $instance;

    public function setUp()
    {
        if (!class_exists('\Doctrine\ODM\MongoDB\DocumentManager')) {
            $this->markTestSkipped('Doctrine MongoDB ODM has to be installed for this test to run.');
        }

        $this->className = AccessToken::class;
        $this->repository = $this->createMock(DocumentRepository::class);
        $this->documentManager = $this->createMock(DocumentManager::class);

        $this->documentManager
            ->expects($this->once())
            ->method('getRepository')
            ->with($this->className)
            ->willReturn($this->repository)
        ;

        $this->instance = new TokenManager($this->documentManager, $this->className);
    }

    public function testFindTokenByToken()
    {
        $randomToken = \random_bytes(5);
        $randomResult = \random_bytes(5);

        $this->repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with([
                'token' => $randomToken,
            ])
            ->willReturn($randomResult)
        ;

        $this->assertSame($randomResult, $this->instance->findTokenByToken($randomToken));
    }

    public function testUpdateTokenPersistsAndFlushes()
    {
        $token = $this->createMock(AccessToken::class);

        $this->documentManager
            ->expects($this->once())
            ->method('persist')
            ->with($token)
        ;

        $this->documentManager
            ->expects($this->once())
            ->method('flush')
            ->with()
        ;

        $this->assertNull($this->instance->updateToken($token));
    }

    public function testGetClass()
    {
        $this->assertSame($this->className, $this->instance->getClass());
    }

    public function testDeleteToken()
    {
        $token = $this->createMock(AccessToken::class);

        $this->documentManager
            ->expects($this->once())
            ->method('remove')
            ->with($token)
            ->willReturn(null)
        ;

        $this->documentManager
            ->expects($this->once())
            ->method('flush')
            ->with()
            ->willReturn(null)
        ;

        $this->assertNull($this->instance->deleteToken($token));
    }

    public function testDeleteExpired()
    {
        $queryBuilder = $this->createMock(Builder::class);

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

        $query = $this->createMock(Query::class);

        $queryBuilder
            ->expects($this->once())
            ->method('getQuery')
            ->with([
                'safe' => true,
            ])
            ->willReturn($query)
        ;

        $data = [
            'n' => \random_bytes(5),
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
