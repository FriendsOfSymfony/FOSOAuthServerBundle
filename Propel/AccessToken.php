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

use FOS\OAuthServerBundle\Model\TokenInterface;
use FOS\OAuthServerBundle\Propel\Map\TokenTableMap;

class AccessToken extends Token implements TokenInterface
{
    /**
     * Constructs a new AccessToken class, setting the class_key column to TokenTableMap::CLASSKEY_2.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setClassKey(TokenTableMap::CLASSKEY_2);
    }
}
