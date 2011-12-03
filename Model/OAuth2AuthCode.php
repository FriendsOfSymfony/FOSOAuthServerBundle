<?php
/*
 *
 */

namespace Alb\OAuth2ServerBundle\Model;

/**
 * @author Richard Fullmer <richard.fullmer@opensoftdev.com>
 */
class OAuth2AuthCode extends OAuth2Token implements OAuth2AuthCodeInterface
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
