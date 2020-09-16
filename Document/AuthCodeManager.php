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
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use FOS\OAuthServerBundle\Model\AuthCodeInterface;
use FOS\OAuthServerBundle\Model\AuthCodeManager as BaseAuthCodeManager;

class AuthCodeManager extends BaseAuthCodeManager
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
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * {@inheritdoc}
     */
    public function findAuthCodeBy(array $criteria)
    {
        return $this->repository->findOneBy($criteria);
    }

    /**
     * {@inheritdoc}
     */
    public function updateAuthCode(AuthCodeInterface $authCode): void
    {
        $this->dm->persist($authCode);
        $this->dm->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function deleteAuthCode(AuthCodeInterface $authCode): void
    {
        $this->dm->remove($authCode);
        $this->dm->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function deleteExpired(): int
    {
        /** @var \MongoDB\Driver\WriteResult */
        $result = $this
            ->repository
            ->createQueryBuilder()
            ->remove()
            ->field('expiresAt')->lt(time())
            ->getQuery(['safe' => true])
            ->execute()
        ;

        return $result->getDeletedCount();
    }
}
