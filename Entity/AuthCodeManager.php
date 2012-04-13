<?php

/*
 * This file is part of the FOSOAuthServerBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\OAuthServerBundle\Entity;

use Doctrine\ORM\EntityManager;
use FOS\OAuthServerBundle\Model\AuthCodeManager as BaseAuthCodeManager;
use FOS\OAuthServerBundle\Model\AuthCodeInterface;

class AuthCodeManager extends BaseAuthCodeManager
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \Doctrine\ORM\EntityRepository
     */
    protected $repository;

    /**
     * @var string
     */
    protected $class;

    /**
     * @param \Doctrine\ORM\EntityManager $em
     * @param string $class
     */
    public function __construct(EntityManager $em, $class)
    {
        $this->em = $em;
        $this->repository = $em->getRepository($class);
        $this->class = $class;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param array $criteria
     */
    public function findAuthCodeBy(array $criteria)
    {
        return $this->repository->findOneBy($criteria);
    }

    /**
     * @param \FOS\OAuthServerBundle\Model\AuthCodeInterface $authCode
     */
    public function updateAuthCode(AuthCodeInterface $authCode)
    {
        $this->em->persist($authCode);
        $this->em->flush();
    }

    /**
     * @param \FOS\OAuthServerBundle\Model\AuthCodeInterface $authCode
     */
    public function deleteAuthCode(AuthCodeInterface $authCode)
    {
        $this->em->remove($authCode);
        $this->em->flush();
    }

    function deleteExpired()
    {
        $qb = $this->repository->createQueryBuilder('a');
        $qb
            ->delete()
            ->where('a.expiresAt < ?1')
            ->setParameters(array(1 => time()));

        return $qb->getQuery()->execute();
    }
}
