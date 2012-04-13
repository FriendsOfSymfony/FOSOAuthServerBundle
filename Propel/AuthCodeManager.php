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

use FOS\OAuthServerBundle\Model\AuthCodeManager as BaseAuthCodeManager;
use FOS\OAuthServerBundle\Model\AuthCodeInterface;

class AuthCodeManager extends BaseAuthCodeManager
{
    protected $class;

    /**
     * @param string $class
     */
    public function __construct($class)
    {
        $this->class = $class;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param array $criteria
     */
    public function findAuthCodeBy(array $criteria)
    {
        $queryClass = $this->class . 'Query';

        return $queryClass::create()
            ->filterByToken($criteria['token'])
            ->findOne();
    }

    /**
     * @param \FOS\OAuthServerBundle\Model\AuthCodeInterface $authCode
     */
    public function updateAuthCode(AuthCodeInterface $authCode)
    {
        $authCode->save();
    }

    /**
     * @param \FOS\OAuthServerBundle\Model\AuthCodeInterface $authCode
     */
    public function deleteAuthCode(AuthCodeInterface $authCode)
    {
        $authCode->delete();
    }

    function deleteExpired()
    {
        $queryClass = $this->class . 'Query';
        $queryClass::create()
            ->filterByExpiresAt(time(), \Criteria::LESS_THAN)
            ->delete();
    }
}
