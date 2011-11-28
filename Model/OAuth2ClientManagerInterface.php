<?php

namespace Alb\OAuth2ServerBundle\Model;

use Alb\OAuth2ServerBundle\Model\OAuth2ClientInterface;

interface OAuth2ClientManagerInterface
{
    function createClient();

    function getClass();

    function findClientBy(array $criteria);

    function findClientByPublicId($publicId);

    function updateClient(OAuth2ClientInterface $client, $andFlush = true);
}

