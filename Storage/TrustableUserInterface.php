<?php
namespace FOS\OAuthServerBundle\Storage;

/**
 * Interface TrustableUserInterface
 * Marks user to be able login without password check
 * @package FOS\OAuthServerBundle\Storage
 */
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