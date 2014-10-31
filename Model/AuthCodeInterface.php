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

use OAuth2\Model\IOAuth2AuthCode;

/**
 * @author Richard Fullmer <richard.fullmer@opensoftdev.com>
 */
interface AuthCodeInterface extends TokenInterface, IOAuth2AuthCode
{
    /**
     * @param string $redirectUri
     */
    public function setRedirectUri($redirectUri);
}
