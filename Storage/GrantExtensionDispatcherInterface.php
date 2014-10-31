<?php

namespace FOS\OAuthServerBundle\Storage;

/**
 * @author Adrien Brault <adrien.brault@gmail.com>
 */
interface GrantExtensionDispatcherInterface
{
    public function setGrantExtension($uri, GrantExtensionInterface $grantExtension);
}
