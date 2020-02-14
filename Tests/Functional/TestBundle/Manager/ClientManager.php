<?php

declare(strict_types=1);

/*
 * This file is part of the FOSOAuthServerBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\OAuthServerBundle\Tests\Functional\TestBundle\Manager;

use FOS\OAuthServerBundle\Model\Client;
use FOS\OAuthServerBundle\Model\ClientInterface;
use FOS\OAuthServerBundle\Model\ClientManager as BaseClientManager;
use ReflectionClass;

class ClientManager extends BaseClientManager
{
    /**
     * {@inheritdoc}
     */
    public function findClientBy(array $criteria): ?Client
    {
        // create an instance as if found
        $client = new Client();

        return $client;
    }

    /**
     * {@inheritdoc}
     */
    public function findClientByPublicId($publicId): ?Client
    {
        if (false === $pos = mb_strpos($publicId, '_')) {
            return null;
        }

        $id = mb_substr($publicId, 0, $pos);
        $randomId = mb_substr($publicId, $pos + 1);

        // create an instance as if found
        $client = new Client();
        $client->setRandomId($randomId);

        $reflectionClass = new ReflectionClass($client);
        $reflectionProperty = $reflectionClass->getProperty('id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($client, $id);

        return $client;
    }

    /**
     * {@inheritdoc}
     */
    public function getClass(): string
    {
        return self::class;
    }

    /**
     * {@inheritdoc}
     */
    public function updateClient(ClientInterface $client): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function deleteClient(ClientInterface $client): void
    {
    }
}
