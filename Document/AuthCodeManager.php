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

use FOS\OAuthServerBundle\Model\AuthCodeManager as BaseAuthCodeManager;
use FOS\OAuthServerBundle\Model\AuthCodeInterface;
use Doctrine\ODM\MongoDB\DocumentManager;

class AuthCodeManager extends BaseAuthCodeManager
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
        $this->dm->persist($authCode);
        $this->dm->flush();
    }

    public function deleteAuthCode(AuthCodeInterface $authCode)
    {
        $this->dm->remove($authCode);
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
