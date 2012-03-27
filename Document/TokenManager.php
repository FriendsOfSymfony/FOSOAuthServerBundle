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

use FOS\OAuthServerBundle\Model\TokenManager as BaseTokenManager;
use Doctrine\ODM\MongoDB\DocumentManager;
use FOS\OAuthServerBundle\Model\TokenInterface;

class TokenManager extends BaseTokenManager
{
    protected $dm;

    protected $repository;

    protected $class;

    public function __construct(DocumentManager $dm, $class)
    {
        $this->dm = $dm;
        $this->repository = $dm->getRepository($class);
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

    public function updateToken(TokenInterface $token, $andFlush = true)
    {
        $this->dm->persist($token);

        if ($andFlush) {
            $this->dm->flush();
        }
    }

    public function deleteToken(TokenInterface $token)
    {
        $this->dm->remove($token);
        $this->dm->flush();
    }

    /**
     * @return boolean
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
