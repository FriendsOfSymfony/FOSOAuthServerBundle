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

use FOS\OAuthServerBundle\Model\TokenInterface;

interface TokenManagerInterface
{
    function createToken();

    function getClass();

    function findTokenBy(array $criteria);

    function findTokenByToken($token);

    function updateToken(TokenInterface $token);

    function deleteToken(TokenInterface $token);

    function deleteExpired();
}

