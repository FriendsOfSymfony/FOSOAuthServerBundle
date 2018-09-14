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

namespace FOS\OAuthServerBundle\Entity;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FOS\OAuthServerBundle\Model\TokenInterface;
use FOS\OAuthServerBundle\Model\TokenManager as BaseTokenManager;

class TokenManager extends BaseTokenManager
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var string
     */
    protected $class;

    public function __construct(EntityManagerInterface $em, $class)
    {
        $this->em = $em;
        $this->class = $class;
    }

    /**
     * {@inheritdoc}
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * {@inheritdoc}
     */
    public function findTokenBy(array $criteria)
    {
        return $this->getRepository()->findOneBy($criteria);
    }

    /**
     * {@inheritdoc}
     */
    public function updateToken(TokenInterface $token)
    {
        $this->em->persist($token);
        $this->em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function deleteToken(TokenInterface $token)
    {
        $this->em->remove($token);
        $this->em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function deleteExpired()
    {
        $qb = $this->getRepository()->createQueryBuilder('t');
        $qb
            ->delete()
            ->where('t.expiresAt < ?1')
            ->setParameters([1 => time()])
        ;

        return $qb->getQuery()->execute();
    }

    private function getRepository(): EntityRepository
    {
        $repository = $this->em->getRepository($this->class);

        return $repository;
    }
}
