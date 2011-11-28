<?php

namespace Alb\OAuth2ServerBundle\Security\Firewall;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

use Alb\OAuth2ServerBundle\Security\Authentification\Token\OAuth2Token;
use OAuth2\OAuth2;
use OAuth2\OAuth2ServerException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * OAuth2Listener class.
 *
 * @package     AlbOAuth2ServerBundle
 * @subpackage  Security
 * @author Arnaud Le Blanc <arnaud.lb@gmail.com>
 */
class OAuth2Listener implements ListenerInterface
{
    /**
     * @var \Symfony\Component\Security\Core\SecurityContextInterface
     */
    protected $securityContext;
    /**
     * @var \Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface
     */
    protected $authenticationManager;

    /**
     * @var OAuth2\OAuth2
     */
    protected $serverService;

    /**
     * @param \Symfony\Component\Security\Core\SecurityContextInterface $securityContext    The security context.
     * @param \Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface $authenticationManager The authentification manager.
     */
    public function __construct(SecurityContextInterface $securityContext, AuthenticationManagerInterface $authenticationManager, OAuth2 $serverService)
    {
        $this->securityContext = $securityContext;
        $this->authenticationManager = $authenticationManager;
        $this->serverService = $serverService;
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event   The event.
     */
    public function handle(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        $oauthToken = $this->serverService->getBearerToken($request, true);

        if (null === $oauthToken) {
            return;
        }

        $token = new OAuth2Token();
        $token->setToken($oauthToken);

        try {
            $returnValue = $this->authenticationManager->authenticate($token);

            if ($returnValue instanceof TokenInterface) {
                return $this->securityContext->setToken($returnValue);
            } else if ($returnValue instanceof Response) {
                return $event->setResponse($response);
            }
        } catch (AuthenticationException $e) {
            if (null !== $p = $e->getPrevious()) {
                $event->setResponse($p->getHttpResponse());
            }
        }
    }
}
