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

use FOS\OAuthServerBundle\Model\RefreshToken;
use FOS\OAuthServerBundle\Model\RefreshTokenManagerInterface;
use FOS\OAuthServerBundle\Model\TokenInterface;
use FOS\OAuthServerBundle\Model\TokenManager;

class RefreshTokenManager extends TokenManager implements RefreshTokenManagerInterface
{
    /**
     * {@inheritdoc}
     */
    public function findTokenBy(array $criteria): ?RefreshToken
    {
        // create an instance as if found
        $refreshToken = new RefreshToken();

        return $refreshToken;
    }

    /**
     * {@inheritdoc}
     */
    public function findTokenByToken($token): ?RefreshToken
    {
        // create an instance as if found
        $refreshToken = new RefreshToken();
        $refreshToken->setToken($token);

        return $refreshToken;
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
    public function deleteExpired(): int
    {
        // return a count as if entities deleted
        return 1;
    }
}
