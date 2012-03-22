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

/**
 * @author Richard Fullmer <richard.fullmer@opensoftdev.com>
 */
interface AuthCodeManagerInterface
{
    /**
     * @return \FOS\OAuthServerBundle\Model\AuthCodeInterface
     */
    function createAuthCode();

    /**
     * @return string
     */
    function getClass();

    /**
     * @param array $criteria
     */
    function findAuthCodeBy(array $criteria);

    /**
     * @param string $token
     *
     * @return \FOS\OAuthServerBundle\Model\AuthCodeInterface
     */
    function findAuthCodeByToken($token);

    /**
     * @param \FOS\OAuthServerBundle\Model\AuthCodeInterface $authCode
     */
    function updateAuthCode(AuthCodeInterface $authCode);

    /**
     * @param \FOS\OAuthServerBundle\Model\AuthCodeInterface $authCode
     */
    function deleteAuthCode(AuthCodeInterface $authCode);

    function deleteExpired();
}
