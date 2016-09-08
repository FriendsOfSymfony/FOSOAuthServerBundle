<?php

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
     *
     * @return TokenInterface
     */
    public function createToken();

    /**
     * Return the class name of the Token.
     *
     * @return string
     */
    public function getClass();

    /**
     * Retrieve a token using a set of criteria.
     *
     * @param array $criteria
     *
     * @return TokenInterface|null
     */
    public function findTokenBy(array $criteria);

    /**
     * Retrieve a token (object) by its token string.
     *
     * @param string $token A token.
     *
     * @return TokenInterface|null
     */
    public function findTokenByToken($token);

    /**
     * Save or update a given token.
     *
     * @param TokenInterface $token The token to save or update.
     */
    public function updateToken(TokenInterface $token);

    /**
     * Delete a given token.
     *
     * @param TokenInterface $token The token to delete.
     */
    public function deleteToken(TokenInterface $token);

    /**
     * Delete expired tokens.
     *
     * @return int The number of tokens deleted.
     */
    public function deleteExpired();
}
