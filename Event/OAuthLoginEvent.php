<?php

namespace FOS\OAuthServerBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Class OAuthLoginEvent
 * @author Jens Hassler <j.hassler@iwf.ch>
 */
class OAuthLoginEvent extends Event
{
    const LOGIN =  'fos_oauth_server.login';

    private $authenticationToken;

    /**
     * Constructor.
     *
     * @param TokenInterface $authenticationToken A TokenInterface instance
     */
    public function __construct(TokenInterface $authenticationToken)
    {
        $this->authenticationToken = $authenticationToken;
    }

    /**
     * Gets the authentication token.
     *
     * @return TokenInterface A TokenInterface instance
     */
    public function getAuthenticationToken()
    {
        return $this->authenticationToken;
    }
} 