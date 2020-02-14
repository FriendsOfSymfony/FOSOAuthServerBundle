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

namespace FOS\OAuthServerBundle\Tests\Functional\TestBundle\Manager;

use FOS\OAuthServerBundle\Model\AuthCode;
use FOS\OAuthServerBundle\Model\AuthCodeInterface;
use FOS\OAuthServerBundle\Model\AuthCodeManager as BaseAuthCodeManager;

class AuthCodeManager extends BaseAuthCodeManager
{
    /**
     * {@inheritdoc}
     */
    public function findAuthCodeBy(array $criteria): AuthCode
    {
        // create an instance as if found
        $authCode = new AuthCode();

        return $authCode;
    }

    /**
     * {@inheritdoc}
     */
    public function findAuthCodeByToken($token)
    {
        // create an instance as if found
        $authCode = new AuthCode();
        $authCode->setToken($token);

        return $authCode;
    }

    /**
     * {@inheritdoc}
     */
    public function getClass(): string
    {
        return self::class;
    }

    /**
     * {@inheritdoc}
     */
    public function updateAuthCode(AuthCodeInterface $authCode): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function deleteAuthCode(AuthCodeInterface $authCode): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function deleteExpired(): void
    {
    }
}
