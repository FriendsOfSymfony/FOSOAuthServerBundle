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

namespace FOS\OAuthServerBundle\Model;

interface ClientManagerInterface
{
    public function createClient(): ClientInterface;

    public function getClass(): string;

    public function findClientBy(array $criteria): ?ClientInterface;

    /**
     * @param mixed $publicId
     */
    public function findClientByPublicId($publicId): ?ClientInterface;

    public function updateClient(ClientInterface $client);

    public function deleteClient(ClientInterface $client);
}
