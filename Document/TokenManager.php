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

namespace FOS\OAuthServerBundle\Document;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\DocumentRepository;
use FOS\OAuthServerBundle\Model\TokenInterface;
use FOS\OAuthServerBundle\Model\TokenManager as BaseTokenManager;

class TokenManager extends BaseTokenManager
{
    /**
     * @var DocumentManager
     */
    protected $dm;

    /**
     * @var DocumentRepository
     */
    protected $repository;

    /**
     * @var string
     */
    protected $class;

    public function __construct(DocumentManager $dm, $class)
    {
        // NOTE: bug in Doctrine, hinting DocumentRepository|ObjectRepository when only DocumentRepository is expected
        /** @var DocumentRepository $repository */
        $repository = $dm->getRepository($class);

        $this->dm = $dm;
        $this->repository = $repository;
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
        $result = $this
            ->repository
            ->createQueryBuilder()
            ->remove()
            ->field('expiresAt')->lt(time())
            ->getQuery(['safe' => true])
            ->execute()
        ;

        return $result['n'];
    }
}
