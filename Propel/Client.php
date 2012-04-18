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

        $this->setAllowedGrantTypes(array(
            OAuth2::GRANT_TYPE_AUTH_CODE,
        ));
        $this->setRandomId(Random::generateToken());
        $this->setSecret(Random::generateToken());
    }

    /**
     * {@inheritdoc}
     */
    public function checkSecret($secret)
    {
        return (null === $this->secret || $secret === $this->secret);
    }

    /**
     * {@inheritdoc}
     */
    public function getPublicId()
    {
        return sprintf('%s_%s', $this->getId(), $this->getRandomId());
    }
}
