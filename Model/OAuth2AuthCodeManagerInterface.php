<?php
/*
 *
 */

namespace Alb\OAuth2ServerBundle\Model;

/**
 * @author Richard Fullmer <richard.fullmer@opensoftdev.com>
 */
interface OAuth2AuthCodeManagerInterface
{
    /**
     * @return OAuth2AuthCodeInterface
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
     * @param $code
     *
     * @return OAuth2AuthCodeInterface
     */
    function findAuthCodeByCode($code);

    /**
     * @param OAuth2AuthCodeInterface $authCode
     * @param bool $andFlush
     */
    function updateAuthCode(OAuth2AuthCodeInterface $authCode, $andFlush = true);
}
