<?php
/*
 *
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
}
