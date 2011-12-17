<?php

namespace FOS\OAuthServerBundle\Model;

interface ClientManagerInterface
{
    function createClient();

    function getClass();

    function findClientBy(array $criteria);

    function findClientByPublicId($publicId);

    function updateClient(ClientInterface $client, $andFlush = true);

    function deleteClient(ClientInterface $client);
}

