<?php

namespace FOS\OAuthServerBundle\Document;

use FOS\OAuthServerBundle\Model\OAuth2ClientManager as BaseOAuth2ClientManager;
use Doctrine\ODM\MongoDB\DocumentManager;
use FOS\OAuthServerBundle\Model\OAuth2ClientInterface;

class OAuth2ClientManager extends BaseOAuth2ClientManager
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

    public function findClientBy(array $criteria)
    {
        return $this->repository->findOneBy($criteria);
    }

    public function updateClient(OAuth2ClientInterface $client, $andFlush = true)
    {
        $this->dm->persist($client);
        
        if ($andFlush) {
            $this->dm->flush();
        }
    }

    public function deleteClient(OAuth2ClientInterface $client)
    {
        $this->dm->remove($client);
        $this->dm->flush();
    }
}

