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

use OAuth2\Model\IOAuth2Token;

interface TokenInterface extends IOAuth2Token
{
    /**
     * @param int $timestamp
     */
    function setExpiresAt($timestamp);

    /**
     * @return int
     */
    function getExpiresAt();

    /**
     * @return Boolean
     */
    function hasExpired();

    /**
     * @return int
     */
    function getExpiresIn();

    /**
     * @param string $token
     */
    function setToken($token);

    /**
     * @return string
     */
    function getToken();

    /**
     * @param string $scope
     */
    function setScope($scope);

    /**
     * @return string
     */
    function getScope();

    /**
     * @param mixed $data
     */
    function setData($data);

    /**
     * @return mixed
     */
    function getData();

    /**
     * @param ClientInterface $client
     */
    function setClient(ClientInterface $client);

    /**
     * @return ClientInterface
     */
    function getClient();
}
