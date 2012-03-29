<?php

/*
 * This file is part of the FOSOAuthServerBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\OAuthServerBundle\Tests\CouchDocument;

use FOS\OAuthServerBundle\CouchDocument\TokenManager;

class TokenManagerTest extends \PHPUnit_Framework_TestCase
{
    private $tokenManager;
    private $dm;
    private $repository;

    const TOKEN_TYPE = 'FOS\OAuthServerBundle\Tests\CouchDocument\DummyToken';

    public function setUp()
    {
        if (!class_exists('Doctrine\ODM\CouchDB\Version')) {
            $this->markTestSkipped('Doctrine CouchDB has to be installed for this test to run.');
        }

        $class = new ClassMetadata(self::TOKEN_TYPE);
        $class->mapField(array('fieldName' => 'username'));

        $this->dm = $this->getMock('Doctrine\ODM\CouchDB\DocumentManager', array('getRepository', 'persist', 'remove', 'flush', 'getClassMetadata'), array(), '', false);
        $this->repository = $this->getMock('Doctrine\ODM\CouchDB\DocumentRepository', array('findBy', 'findAll'), array(), '', false);
        $this->dm->expects($this->any())
                 ->method('getRepository')
                 ->with($this->equalTo(self::TOKEN_TYPE))
                 ->will($this->returnValue($this->repository));
        $this->dm->expects($this->any())
                 ->method('getClassMetadata')
                 ->with($this->equalTo(self::TOKEN_TYPE))
                 ->will($this->returnValue($class));
        $this->tokenManager = new TokenManager($this->dm, self::TOKEN_TYPE);
    }

    protected function tearDown()
    {
        unset($this->tokenManager);
        unset($this->dm);
        unset($this->repository);
    }
}

class DummyToken extends \FOS\OAuthServerBundle\Document\AccessToken
{

}