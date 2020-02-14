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

use FOS\OAuthServerBundle\Model\AccessToken;
use FOS\OAuthServerBundle\Model\AccessTokenManagerInterface;
use FOS\OAuthServerBundle\Model\TokenInterface;
use FOS\OAuthServerBundle\Model\TokenManager;

class AccessTokenManager extends TokenManager implements AccessTokenManagerInterface
{
    /**
     * {@inheritdoc}
     */
    public function findTokenBy(array $criteria): ?AccessToken
    {
        // create an instance as if found
        $accessToken = new AccessToken();

        return $accessToken;
    }

    /**
     * {@inheritdoc}
     */
    public function findTokenByToken($token): ?AccessToken
    {
        // create an instance as if found
        $accessToken = new AccessToken();
        $accessToken->setToken($token);

        return $accessToken;
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
    public function updateToken(TokenInterface $token): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function deleteToken(TokenInterface $token): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function deleteExpired(): void
    {
    }
}
