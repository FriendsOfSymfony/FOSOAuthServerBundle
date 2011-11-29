<?php

namespace Alb\OAuth2ServerBundle\Tests\Entity;

use Alb\OAuth2ServerBundle\Entity\OAuth2TokenManager;
use Alb\OAuth2ServerBundle\Entity\OAuth2AccessToken;

class OAuth2TokenManagerTest extends \PHPUnit_Framework_TestCase
{
    protected $em;
    protected $repository;
    protected $class;

    public function setUp()
    {
        $this->class = 'Alb\OAuth2ServerBundle\Entity\OAuth2AccessToken';
        $this->repository = $this->getMock('Doctrine\ORM\EntityRepository', array(), array(), '', false);
        $this->em = $this->getMock('Doctrine\ORM\EntityManager', array(), array(), '', false);
        $this->em->expects($this->once())
            ->method('getRepository')
            ->with($this->class)
            ->will($this->returnValue($this->repository));

        $this->manager = new OAuth2TokenManager($this->em, $this->class);
    }

    public function testUpdateTokenPersistsAndFlushes()
    {
        $token = new OAuth2AccessToken;

        $this->em->expects($this->once())
            ->method('persist')
            ->with($token);
        $this->em->expects($this->once())
            ->method('flush')
            ->with();

        $this->manager->updateToken($token);
    }

    public function testUpdateTokenPersistsAndDoesntFlush()
    {
        $token = new OAuth2AccessToken;

        $this->em->expects($this->once())
            ->method('persist')
            ->with($token);
        $this->em->expects($this->never())
            ->method('flush');

        $this->manager->updateToken($token, false);
    }

}

