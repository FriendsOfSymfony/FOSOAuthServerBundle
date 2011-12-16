<?php

namespace FOS\OAuthServerBundle\Model;

use FOS\OAuthServerBundle\Model\OAuth2ClientInterface;

interface OAuth2ClientManagerInterface
{
    function createClient();

    function getClass();

    function findClientBy(array $criteria);

    function findClientByPublicId($publicId);

    function updateClient(OAuth2ClientInterface $client, $andFlush = true);

    function deleteClient(OAuth2ClientInterface $client);
}

