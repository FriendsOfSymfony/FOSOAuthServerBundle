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
    protected $class;

    public function __construct($class)
    {
        $this->class = $class;
    }

    public function getClass()
    {
        return $this->class;
    }

    public function findTokenBy(array $criteria)
    {
        $queryClass = $this->class . 'Query';

        return $queryClass::create()
            ->filterByToken($criteria['token'])
            ->findOne();
    }

    public function updateToken(TokenInterface $token)
    {
        $token->save();
    }

    public function deleteToken(TokenInterface $token)
    {
        $token->remove();
    }

    function deleteExpired()
    {
        $queryClass = $this->class . 'Query';
        $queryClass::create()
            ->filterByExpiresAt(time(), \Criteria::LESS_THAN)
            ->delete();
    }
}
