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
    /**
     * @return ClientInterface
     */
    public function createClient();

    /**
     * @return string
     */
    public function getClass();

    /**
     * @return ClientInterface|null
     */
    public function findClientBy(array $criteria);

    /**
     * @param mixed $publicId
     *
     * @return ClientInterface|null
     */
    public function findClientByPublicId($publicId);

    public function updateClient(ClientInterface $client);

    public function deleteClient(ClientInterface $client);
}
