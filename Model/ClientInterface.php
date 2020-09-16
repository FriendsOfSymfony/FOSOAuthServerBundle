<?php

declare(strict_types=1);

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
     * @param string $random
     */
    public function setRandomId($random);

    /**
     * @return string
     */
    public function getRandomId();

    /**
     * @param string $secret
     */
    public function setSecret($secret);

    /**
     * @param string $secret
     *
     * @return bool
     */
    public function checkSecret($secret);

    /**
     * @return string
     */
    public function getSecret();

    public function setRedirectUris(array $redirectUris);

    public function setAllowedGrantTypes(array $grantTypes);

    /**
     * @return array
     */
    public function getAllowedGrantTypes();
}
