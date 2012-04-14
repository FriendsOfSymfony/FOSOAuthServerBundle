<?php

/*
 * This file is part of the FOSOAuthServerBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\OAuthServerBundle\Propel;

use FOS\OAuthServerBundle\Model\ClientManager as BaseClientManager;
use FOS\OAuthServerBundle\Model\ClientInterface;

class ClientManager extends BaseClientManager
{
    protected $class;

    public function __construct($class)
    {
        $this->class = $class;
    }

    public function getClass()
    {
        return $this->class;
    }

    public function findClientBy(array $criteria)
    {
        $queryClass = $this->class . 'Query';

        return $queryClass::create()
            ->filterById($criteria['id'])
            ->filterByRandomId($criteria['randomId'])
            ->findOne();
    }

    public function updateClient(ClientInterface $client)
    {
        $client->save();
    }

    public function deleteClient(ClientInterface $client)
    {
        $client->delete();
    }
}
