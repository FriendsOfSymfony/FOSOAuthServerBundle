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

use FOS\OAuthServerBundle\Model\TokenManager as BaseTokenManager;
use FOS\OAuthServerBundle\Model\TokenInterface;

class TokenManager extends BaseTokenManager
{
    /**
     * @var string
     */
    protected $class;

    /**
     * @param string $class A class name.
     */
    public function __construct($class)
    {
        $this->class = $class;
    }

    /**
     * {@inheritdoc}
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * {@inheritdoc}
     */
    public function findTokenBy(array $criteria)
    {
        if (!isset($criteria['token'])) {
            return null;
        }

        $queryClass = $this->class . 'Query';

        return $queryClass::create()
            ->filterByToken($criteria['token'])
            ->findOne();
    }

    /**
     * {@inheritdoc}
     */
    public function updateToken(TokenInterface $token)
    {
        $token->save();
    }

    /**
     * {@inheritdoc}
     */
    public function deleteToken(TokenInterface $token)
    {
        $token->delete();
    }

    /**
     * {@inheritdoc}
     */
    public function deleteExpired()
    {
        $queryClass = $this->class . 'Query';

        return $queryClass::create()
            ->filterByExpiresAt(time(), \Criteria::LESS_THAN)
            ->delete();
    }
}
