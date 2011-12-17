<?php

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
}

