<?php

namespace FOS\OAuthServerBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class OAuthTokenEvent extends Event
{
    const POST_ACCESS_TOKEN_GRANT = 'fos_oauth_server.post_access_token_grant';

    /**
     * @var array keys: ["access_token", "expires_in", "token_type", "scope"],
     */
    private $accessTokenData;

    /**
     * @param array $accessTokenData
     */
    public function __construct(array $accessTokenData)
    {
        $this->accessTokenData = $accessTokenData;
    }

    /**
     * @return array
     */
    public function getAccessTokenData()
    {
        return $this->accessTokenData;
    }
}
