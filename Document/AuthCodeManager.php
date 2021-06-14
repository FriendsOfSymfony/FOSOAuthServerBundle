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
use FOS\OAuthServerBundle\Model\AuthCodeInterface;
use FOS\OAuthServerBundle\Model\AuthCodeManager as BaseAuthCodeManager;

class AuthCodeManager extends BaseAuthCodeManager
{
    /**
     * @var DocumentManager
     */
    protected $dm;

    /**
     * @var string
     */
    protected $class;

    public function __construct(DocumentManager $dm, $class)
    {
        $this->dm = $dm;
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
        return $this->dm->getRepository($this->class)->findOneBy($criteria);
    }

    /**
     * {@inheritdoc}
     */
    public function updateAuthCode(AuthCodeInterface $authCode)
    {
        $this->dm->persist($authCode);
        $this->dm->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function deleteAuthCode(AuthCodeInterface $authCode)
    {
        $this->dm->remove($authCode);
        $this->dm->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function deleteExpired()
    {
        /** @var \Doctrine\ODM\MongoDB\DocumentRepository $repository */
        $repository = $this->dm->getRepository($this->class);

        $result = $repository
            ->createQueryBuilder()
            ->remove()
            ->field('expiresAt')->lt(time())
            ->getQuery(['safe' => true])
            ->execute()
        ;

        return $result['n'];
    }
}
