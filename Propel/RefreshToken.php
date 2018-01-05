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

namespace FOS\OAuthServerBundle\Propel;

use FOS\OAuthServerBundle\Model\TokenInterface;

class RefreshToken extends Token implements TokenInterface
{
    /**
     * Constructs a new RefreshToken class, setting the class_key column to TokenPeer::CLASSKEY_3.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setClassKey(TokenPeer::CLASSKEY_3);
    }
}
