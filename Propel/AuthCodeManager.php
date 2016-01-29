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
    /**
     * @var string
     */
    protected $class;

    /**
     * @param string $class
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
    public function findAuthCodeBy(array $criteria)
    {
        if (!isset($criteria['token'])) {
            return;
        }

        $queryClass = $this->class.'Query';

        return $queryClass::create()
            ->filterByToken($criteria['token'])
            ->findOne();
    }

    /**
     * {@inheritdoc}
     */
    public function updateAuthCode(AuthCodeInterface $authCode)
    {
        $authCode->save();
    }

    /**
     * {@inheritdoc}
     */
    public function deleteAuthCode(AuthCodeInterface $authCode)
    {
        $authCode->delete();
    }

    /**
     * {@inheritdoc}
     */
    public function deleteExpired()
    {
        $queryClass = $this->class.'Query';

        return $queryClass::create()
            ->filterByExpiresAt(time(), \Criteria::LESS_THAN)
            ->delete();
    }
}
