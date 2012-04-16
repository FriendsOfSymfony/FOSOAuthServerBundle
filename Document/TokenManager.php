<?php

/*
 * This file is part of the FOSOAuthServerBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\OAuthServerBundle\Document;

use Doctrine\ODM\MongoDB\DocumentManager;
use FOS\OAuthServerBundle\Model\TokenInterface;
use FOS\OAuthServerBundle\Model\TokenManager as BaseTokenManager;

class TokenManager extends BaseTokenManager
{
    /**
     * @var \Doctrine\ODM\MongoDB\DocumentManager
     */
    protected $dm;

    /**
     */
    protected $repository;

    /**
     * @var string
     */
    protected $class;

    public function __construct(DocumentManager $dm, $class)
    {
        $this->dm = $dm;
        $this->repository = $dm->getRepository($class);
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
        return $this->repository->findOneBy($criteria);
    }

    /**
     * {@inheritdoc}
     */
    public function updateToken(TokenInterface $token)
    {
        $this->dm->persist($token);
        $this->dm->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function deleteToken(TokenInterface $token)
    {
        $this->dm->remove($token);
        $this->dm->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function deleteExpired()
    {
        return $this
            ->repository
            ->createQueryBuilder()
            ->findAndRemove()
            ->field('expiresAt')->lt(time())
            ->getQuery()
            ->execute();
    }
}
