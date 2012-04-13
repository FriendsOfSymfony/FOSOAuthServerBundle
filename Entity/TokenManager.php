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
use FOS\OAuthServerBundle\Model\TokenManager as BaseTokenManager;
use FOS\OAuthServerBundle\Model\TokenInterface;

class TokenManager extends BaseTokenManager
{
    protected $em;

    protected $repository;

    protected $class;

    public function __construct(EntityManager $em, $class)
    {
        $this->em = $em;
        $this->repository = $em->getRepository($class);
        $this->class = $class;
    }

    public function getClass()
    {
        return $this->class;
    }

    public function findTokenBy(array $criteria)
    {
        return $this->repository->findOneBy($criteria);
    }

    public function updateToken(TokenInterface $token)
    {
        $this->em->persist($token);
        $this->em->flush();
    }

    public function deleteToken(TokenInterface $token)
    {
        $this->em->remove($token);
        $this->em->flush();
    }

    function deleteExpired()
    {
        $qb = $this->repository->createQueryBuilder('t');
        $qb
            ->delete()
            ->where('t.expiresAt < ?1')
            ->setParameters(array(1 => time()));

        return $qb->getQuery()->execute();
    }
}
