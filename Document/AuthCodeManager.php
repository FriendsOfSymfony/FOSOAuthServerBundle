<?php

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
    function getClass()
    {
        return $this->class;
    }

    /**
     * @param array $criteria
     */
    function findAuthCodeBy(array $criteria)
    {
        return $this->repository->findOneBy($criteria);
    }

    /**
     * @param AuthCodeInterface $authCode
     * @param boolean $andFlush
     */
    function updateAuthCode(AuthCodeInterface $authCode, $andFlush = true)
    {
        $this->dm->persist($authCode);

        if ($andFlush) {
            $this->dm->flush();
        }
    }

    function deleteAuthCode(AuthCodeInterface $authCode)
    {
        $this->dm->remove($authCode);
        $this->dm->flush();
    }
}

