<?php

namespace FOS\OAuthServerBundle\Entity;

use FOS\OAuthServerBundle\Model\OAuth2ClientManager as BaseOAuth2ClientManager;
use Doctrine\ORM\EntityManager;
use FOS\OAuthServerBundle\Model\OAuth2ClientInterface;

class OAuth2ClientManager extends BaseOAuth2ClientManager
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

    public function findClientBy(array $criteria)
    {
        return $this->repository->findOneBy($criteria);
    }

    public function updateClient(OAuth2ClientInterface $client, $andFlush = true)
    {
        $this->em->persist($client);
        
        if ($andFlush) {
            $this->em->flush();
        }
    }

    public function deleteClient(OAuth2ClientInterface $client)
    {
        $this->em->remove($client);
        $this->em->flush();
    }
}

