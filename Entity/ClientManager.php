<?php

/*
 * This file is part of the FOSOAuthServerBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\OAuthServerBundle\Entity;

use Doctrine\ORM\EntityManager;
use FOS\OAuthServerBundle\Model\ClientManager as BaseClientManager;
use FOS\OAuthServerBundle\Model\ClientInterface;

class ClientManager extends BaseClientManager
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

    public function updateClient(ClientInterface $client)
    {
        $this->em->persist($client);
        $this->em->flush();
    }

    public function deleteClient(ClientInterface $client)
    {
        $this->em->remove($client);
        $this->em->flush();
    }
}
