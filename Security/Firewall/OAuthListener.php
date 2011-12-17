<?php

/*
 * This file is part of the FOSOAuthServerBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\OAuthServerBundle\Security\Firewall;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use FOS\OAuthServerBundle\Security\Authentification\Token\OAuthToken;
use OAuth2\OAuth2;
use OAuth2\OAuth2ServerException;

/**
 * OAuthListener class.
 *
 * @author Arnaud Le Blanc <arnaud.lb@gmail.com>
 */
class OAuthListener implements ListenerInterface
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
     * @var \OAuth2\OAuth2
     */
    protected $serverService;

    /**
     * @param \Symfony\Component\Security\Core\SecurityContextInterface $securityContext    The security context.
     * @param \Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface $authenticationManager The authentification manager.
     * @param \OAuth2\OAuth2 $serverService
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

        $token = new OAuthToken();
        $token->setToken($oauthToken);

        try {
            $returnValue = $this->authenticationManager->authenticate($token);

            if ($returnValue instanceof TokenInterface) {
                return $this->securityContext->setToken($returnValue);
            } elseif ($returnValue instanceof Response) {
                return $event->setResponse($returnValue);
            }
        } catch (AuthenticationException $e) {
            if (null !== $p = $e->getPrevious()) {
                $event->setResponse($p->getHttpResponse());
            }
        }
    }
}
