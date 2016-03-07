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

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use FOS\OAuthServerBundle\Model\AuthCodeInterface;
use FOS\OAuthServerBundle\Model\AuthCodeManager as BaseAuthCodeManager;

class AuthCodeManager extends BaseAuthCodeManager
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var string
     */
    protected $class;

    /**
     * @param EntityManager $em
     * @param string        $class
     */
    public function __construct(ObjectManager $em, $class)
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
    public function findAuthCodeBy(array $criteria)
    {
        return $this->em->getRepository($this->class)->findOneBy($criteria);
    }

    /**
     * {@inheritdoc}
     */
    public function updateAuthCode(AuthCodeInterface $authCode)
    {
        $this->em->persist($authCode);
        $this->em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function deleteAuthCode(AuthCodeInterface $authCode)
    {
        $this->em->remove($authCode);
        $this->em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function deleteExpired()
    {
        $qb = $this->em->getRepository($this->class)->createQueryBuilder('a');
        $qb
            ->delete()
            ->where('a.expiresAt < ?1')
            ->setParameters(array(1 => time()));

        return $qb->getQuery()->execute();
    }
}
