<?php

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
    function createClient();

    /**
     * @return string
     */
    function getClass();

    /**
     * @return ClientInterface
     */
    function findClientBy(array $criteria);

    /**
     * @return ClientInterface
     */
    function findClientByPublicId($publicId);

    /**
     * @param ClientInterface
     */
    function updateClient(ClientInterface $client);

    /**
     * @param ClientInterface
     */
    function deleteClient(ClientInterface $client);
}
