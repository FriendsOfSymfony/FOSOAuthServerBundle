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

abstract class AuthCodeManager implements AuthCodeManagerInterface
{
    /**
     * {@inheritdoc}
     */
    public function createAuthCode()
    {
        $class = $this->getClass();

        return new $class();
    }

    /**
     * {@inheritdoc}
     */
    public function findAuthCodeByToken($token)
    {
        return $this->findAuthCodeBy(['token' => $token]);
    }
}
