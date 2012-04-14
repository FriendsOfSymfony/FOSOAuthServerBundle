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

use FOS\OAuthServerBundle\Entity\TokenManager;
use FOS\OAuthServerBundle\Entity\AccessToken;

class TokenManagerTest extends \PHPUnit_Framework_TestCase
{
    protected $em;

    protected $repository;

    protected $class;

    protected $manager;

    public function setUp()
    {
        $this->class = 'FOS\OAuthServerBundle\Entity\AccessToken';
        $this->repository = $this->getMock('Doctrine\ORM\EntityRepository', array(), array(), '', false);
        $this->em = $this->getMock('Doctrine\ORM\EntityManager', array(), array(), '', false);
        $this->em->expects($this->once())
            ->method('getRepository')
            ->with($this->class)
            ->will($this->returnValue($this->repository));

        $this->manager = new TokenManager($this->em, $this->class);
    }

    public function testUpdateTokenPersistsAndFlushes()
    {
        $token = new AccessToken();

        $this->em->expects($this->once())
            ->method('persist')
            ->with($token);
        $this->em->expects($this->once())
            ->method('flush')
            ->with();

        $this->manager->updateToken($token);
    }
}
