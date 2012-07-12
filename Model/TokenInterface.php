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
use Symfony\Component\Security\Core\User\UserInterface;

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
     * @param string $token
     */
    function setToken($token);

    /**
     * @param string $scope
     */
    function setScope($scope);

    /**
     * @param UserInterface $user
     */
    function setUser(UserInterface $user = null);

    /**
     * @return UserInterface
     */
    function getUser();

    /**
     * @param ClientInterface $client
     */
    function setClient(ClientInterface $client);
}
