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

use FOS\OAuthServerBundle\Model\AuthCodeInterface;
use FOS\OAuthServerBundle\Propel\om\BaseAuthCode;

class AuthCode extends BaseAuthCode implements AuthCodeInterface
{
    /**
     * @var mixed
     */
    protected $data;

    /**
     * {@inheritdoc}
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        return $this->data;
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
}
