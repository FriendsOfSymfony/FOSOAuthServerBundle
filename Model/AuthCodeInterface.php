<?php
/*
 *
 */

namespace FOS\OAuthServerBundle\Model;

use OAuth2\Model\IOAuth2AuthCode;

/**
 * @author Richard Fullmer <richard.fullmer@opensoftdev.com>
 */
interface AuthCodeInterface extends TokenInterface, IOAuth2AuthCode
{
}
