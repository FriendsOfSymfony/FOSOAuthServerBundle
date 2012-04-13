<?php

namespace FOS\OAuthServerBundle\Propel;

class RefreshToken extends Token
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
