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

interface TokenManagerInterface
{
    /**
     * Create a new TokenInterface.
     */
    public function createToken(): TokenInterface;

    /**
     * Return the class name of the Token.
     */
    public function getClass(): string;

    /**
     * Retrieve a token using a set of criteria.
     */
    public function findTokenBy(array $criteria): ?TokenInterface;

    /**
     * Retrieve a token (object) by its token string.
     */
    public function findTokenByToken(string $token): ?TokenInterface;

    /**
     * Save or update a given token.
     */
    public function updateToken(TokenInterface $token);

    /**
     * Delete a given token.
     */
    public function deleteToken(TokenInterface $token);

    /**
     * Delete expired tokens.
     */
    public function deleteExpired(): int;
}
