<?php

namespace FOS\OAuthServerBundle\Document;

use FOS\OAuthServerBundle\Model\ClientManager as BaseClientManager;
use Doctrine\ODM\MongoDB\DocumentManager;
use FOS\OAuthServerBundle\Model\ClientInterface;

class ClientManager extends BaseClientManager
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

    public function updateClient(ClientInterface $client, $andFlush = true)
    {
        $this->dm->persist($client);

        if ($andFlush) {
            $this->dm->flush();
        }
    }

    public function deleteClient(ClientInterface $client)
    {
        $this->dm->remove($client);
        $this->dm->flush();
    }
}

