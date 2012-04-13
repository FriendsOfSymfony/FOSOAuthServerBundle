<?php

/*
 * This file is part of the FOSOAuthServerBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\OAuthServerBundle\Propel;

use FOS\OAuthServerBundle\Propel\om\BaseClient;

use FOS\OAuthServerBundle\Model\ClientInterface;
use FOS\OAuthServerBundle\Util\Random;
use OAuth2\OAuth2;

class Client extends BaseClient implements ClientInterface
{
    public function __construct()
    {
        parent::__construct();

        $this->redirectUris = array();
        $this->allowedGrantTypes = array(
            OAuth2::GRANT_TYPE_AUTH_CODE,
        );
        $this->randomId = Random::generateToken();
        $this->secret   = Random::generateToken();
    }

    /**
     * {@inheritdoc}
     */
    public function checkSecret($secret)
    {
        return $this->secret === NULL || $secret === $this->secret;
    }

    /**
     * {@inheritdoc}
     */
    public function getPublicId()
    {
        return "{$this->getId()}_{$this->getRandomId()}";
    }
}
