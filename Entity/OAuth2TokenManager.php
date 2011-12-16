<?php

namespace FOS\OAuthServerBundle\Entity;

use FOS\OAuthServerBundle\Model\OAuth2TokenManager as BaseOAuth2TokenManager;
use Doctrine\ORM\EntityManager;
use FOS\OAuthServerBundle\Model\OAuth2TokenInterface;

class OAuth2TokenManager extends BaseOAuth2TokenManager
{
    protected $em;

    protected $repository;

    protected $class;

    public function __construct(EntityManager $em, $class)
    {
        $this->em = $em;
        $this->repository = $em->getRepository($class);
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
        $this->em->persist($token);

        if ($andFlush) {
            $this->em->flush();
        }
    }

    public function deleteToken(OAuth2TokenInterface $token)
    {
        $this->em->remove($token);
        $this->em->flush();
    }
}

