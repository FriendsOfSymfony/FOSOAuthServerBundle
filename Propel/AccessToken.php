<?php

namespace FOS\OAuthServerBundle\Propel;

class AccessToken extends Token
{
    /**
	 * Constructs a new AccessToken class, setting the class_key column to TokenPeer::CLASSKEY_2.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->setClassKey(TokenPeer::CLASSKEY_2);
	}
}
