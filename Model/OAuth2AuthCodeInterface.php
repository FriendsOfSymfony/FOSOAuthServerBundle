<?php
/*
 *
 */

namespace FOS\OAuthServerBundle\Model;

use OAuth2\Model\IOAuth2AuthCode;

/**
 * @author Richard Fullmer <richard.fullmer@opensoftdev.com>
 */
interface OAuth2AuthCodeInterface extends OAuth2TokenInterface, IOAuth2AuthCode
{
}
