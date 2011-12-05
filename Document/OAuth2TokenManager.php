<?php

namespace Alb\OAuth2ServerBundle\Document;

use Alb\OAuth2ServerBundle\Model\OAuth2TokenManager as BaseOAuth2TokenManager;
use Doctrine\ODM\MongoDB\DocumentManager;
use Alb\OAuth2ServerBundle\Model\OAuth2TokenInterface;

class OAuth2TokenManager extends BaseOAuth2TokenManager
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

    public function updateToken(OAuth2TokenInterface $token, $andFlush = true)
    {
        $this->dm->persist($token);

        if ($andFlush) {
            $this->dm->flush();
        }
    }

    public function deleteToken(OAuth2TokenInterface $token)
    {
        $this->dm->remove($token);
        $this->dm->flush();
    }
}

