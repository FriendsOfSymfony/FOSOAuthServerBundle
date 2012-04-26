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
    /**
     * @var string
     */
    protected $class;

    public function __construct($class)
    {
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
    public function findClientBy(array $criteria)
    {
        if (!isset($criteria['id']) || !isset($criteria['randomId'])) {
            return null;
        }

        $queryClass = $this->class . 'Query';

        return $queryClass::create()
            ->filterById($criteria['id'])
            ->filterByRandomId($criteria['randomId'])
            ->findOne();
    }

    /**
     * {@inheritdoc}
     */
    public function updateClient(ClientInterface $client)
    {
        $client->save();
    }

    /**
     * {@inheritdoc}
     */
    public function deleteClient(ClientInterface $client)
    {
        $client->delete();
    }
}
