<?php

namespace FOS\OAuthServerBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use FOS\OAuthServerBundle\Model\AccessTokenInterface;

/**
 *
 * @author Konrad Rozner
 */
class OAuthTokenEvent extends Event
{
    const POST_ACCESS_TOKEN_GRANT = 'fos_oauth_server.post_access_token_grant';

    /**
     * @var \FOS\OAuthServerBundle\Model\AccessTokenInterface
     */
    private $accessToken;

    /**
     * @param AccessTokenInterface $accessToken
     */
    public function __construct(AccessTokenInterface $accessToken)
    {
        $this->accessToken = $accessToken;
    }

    /**
     * @return AccessTokenInterface
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }
}
