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

use FOS\OAuthServerBundle\Document\TokenManager;
use FOS\OAuthServerBundle\Document\AccessToken;

class TokenManagerTest extends \PHPUnit_Framework_TestCase
{
    protected $class;
    protected $dm;
    protected $repository;
    protected $manager;

    public function setUp()
    {
        if (!class_exists('\Doctrine\ODM\MongoDB\DocumentManager')) {
            $this->markTestSkipped('Doctrine MongoDB ODM has to be installed for this test to run.');
        }

        $this->class = 'FOS\OAuthServerBundle\Document\AccessToken';
        $this->repository = $this->getMock('Doctrine\ODM\MongoDB\DocumentRepository', array(), array(), '', false);
        $this->dm = $this->getMock('Doctrine\ODM\MongoDB\DocumentManager', array(), array(), '', false);
        $this->dm->expects($this->once())
            ->method('getRepository')
            ->with($this->class)
            ->will($this->returnValue($this->repository));

        $this->manager = new TokenManager($this->dm, $this->class);
    }

    public function testFindTokenByToken()
    {
        $manager = $this->getMockBuilder('FOS\OAuthServerBundle\Document\TokenManager')
            ->disableOriginalConstructor()
            ->setMethods(array('findTokenBy'))
            ->getMock();

        $manager->expects($this->once())
            ->method('findTokenBy')
            ->with($this->equalTo(array('token' => '1234')));

        $manager->findTokenByToken('1234');
    }

    public function testUpdateTokenPersistsAndFlushes()
    {
        $token = new AccessToken();

        $this->dm->expects($this->once())
            ->method('persist')
            ->with($token);
        $this->dm->expects($this->once())
            ->method('flush')
            ->with();

        $this->manager->updateToken($token);
    }
}
