<?php

namespace FOS\OAuthServerBundle\Storage;

use OAuth2\Model\IOAuth2Client;

/**
 * @author Adrien Brault <adrien.brault@gmail.com>
 */
interface GrantExtensionInterface
{
    /**
     * @see OAuth2\IOAuth2GrantExtension::checkGrantExtension
     */
    public function checkGrantExtension(IOAuth2Client $client, array $inputData, array $authHeaders);
}
