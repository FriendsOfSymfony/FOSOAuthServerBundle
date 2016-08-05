<?php
namespace FOS\OAuthServerBundle\Storage;

interface TrustableUserInterface
{
    /**
     * @return boolean
     */
    public function isTrusted(): bool;

    /**
     * @param boolean $isTrusted
     */
    public function setTrusted(bool $isTrusted);
}