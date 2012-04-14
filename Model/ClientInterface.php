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

use OAuth2\Model\IOAuth2Client;

interface ClientInterface extends IOAuth2Client
{
    function getId();

    function setRandomId($random);

    function getRandomId();

    function setSecret($secret);

    function checkSecret($secret);

    function getSecret();

    function setRedirectUris(array $redirectUris);

    function getRedirectUris();

    function setAllowedGrantTypes(array $grantTypes);

    function getAllowedGrantTypes();
}
