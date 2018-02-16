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

namespace FOS\OAuthServerBundle\Propel;

use FOS\OAuthServerBundle\Model\TokenInterface;
use FOS\OAuthServerBundle\Propel\om\BaseToken;

abstract class Token extends BaseToken implements TokenInterface
{
    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        return $this->getUser();
    }

    /**
     * {@inheritdoc}
     */
    public function getExpiresIn()
    {
        if ($this->getExpiresAt()) {
            return $this->getExpiresAt() - time();
        }

        return PHP_INT_MAX;
    }

    /**
     * {@inheritdoc}
     */
    public function hasExpired()
    {
        if ($this->getExpiresAt()) {
            return time() > $this->getExpiresAt();
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getClientId()
    {
        return $this->getClient()->getPublicId();
    }
}
