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
    /**
     * @return string
     */
    function getPublicId();

    /**
     * @param string $random
     */
    function setRandomId($random);

    /**
     * @return string
     */
    function getRandomId();

    /**
     * @param string $secret
     */
    function setSecret($secret);

    /**
     * @param $secret
     * @return Boolean
     */
    function checkSecret($secret);

    /**
     * @return string
     */
    function getSecret();

    /**
     * @param array $redirectUris
     */
    function setRedirectUris(array $redirectUris);

    /**
     * @return array
     */
    function getRedirectUris();

    /**
     * @param array $grantTypes
     */
    function setAllowedGrantTypes(array $grantTypes);

    /**
     * @return array
     */
    function getAllowedGrantTypes();
}
