<?php
/*
 *
 */

namespace FOS\OAuthServerBundle\Model;

/**
 * @author Richard Fullmer <richard.fullmer@opensoftdev.com>
 */
class AuthCode extends Token implements AuthCodeInterface
{
    protected $redirectUri;

    public function setRedirectUri($redirectUri)
    {
        $this->redirectUri = $redirectUri;
    }

    public function getRedirectUri()
    {
        return $this->redirectUri;
    }
}
