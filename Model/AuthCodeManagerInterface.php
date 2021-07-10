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

/**
 * @author Richard Fullmer <richard.fullmer@opensoftdev.com>
 */
interface AuthCodeManagerInterface
{
    /**
     * Create a new auth code.
     */
    public function createAuthCode(): AuthCodeInterface;

    /**
     * Return the class name.
     */
    public function getClass(): string;

    /**
     * Retrieve an auth code using a set of criteria.
     */
    public function findAuthCodeBy(array $criteria): ?AuthCodeInterface;

    /**
     * Retrieve an auth code by its token.
     */
    public function findAuthCodeByToken(string $token): ?AuthCodeInterface;

    /**
     * Update a given auth code.
     */
    public function updateAuthCode(AuthCodeInterface $authCode);

    /**
     * Delete a given auth code.
     */
    public function deleteAuthCode(AuthCodeInterface $authCode);

    /**
     * Delete expired auth codes.
     */
    public function deleteExpired(): int;
}
