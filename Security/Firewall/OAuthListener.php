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

use FOS\OAuthServerBundle\Security\Authentication\Token\OAuthToken;
use OAuth2\OAuth2;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;

/**
 * OAuthListener class.
 *
 * @author Arnaud Le Blanc <arnaud.lb@gmail.com>
 */
class OAuthListener implements ListenerInterface
{
    /**
     * @var SecurityContextInterface
     *
     * @deprecated since 1.4.3, to be removed in 2.0
     */
    protected $securityContext;

    /**
     * @var AuthenticationManagerInterface
     */
    protected $authenticationManager;

    /**
     * @var OAuth2
     */
    protected $serverService;

    /**
     * @var TokenStorageInterface|SecurityContextInterface
     */
    private $tokenStorage;

    /**
     * @param TokenStorageInterface|SecurityContextInterface    $tokenStorage
     * @param AuthenticationManagerInterface                    $authenticationManager The authentication manager.
     * @param OAuth2                                            $serverService
     */
    public function __construct($tokenStorage, AuthenticationManagerInterface $authenticationManager, OAuth2 $serverService)
    {
        if (!$tokenStorage instanceof TokenStorageInterface && !$tokenStorage instanceof SecurityContextInterface) {
            throw new \InvalidArgumentException(sprintf('Argument 1 of %s should be an instance of TokenStorageInterface or SecurityContextInterface', __CLASS__));
        }

        $this->tokenStorage = $tokenStorage;
        // Property name BC
        $this->securityContext = $this->tokenStorage;
        $this->authenticationManager = $authenticationManager;
        $this->serverService = $serverService;
    }

    /**
     * @param GetResponseEvent $event The event.
     */
    public function handle(GetResponseEvent $event)
    {
        if (null === $oauthToken = $this->serverService->getBearerToken($event->getRequest(), true)) {
            return;
        }

        $token = new OAuthToken();
        $token->setToken($oauthToken);

        try {
            $returnValue = $this->authenticationManager->authenticate($token);

            if ($returnValue instanceof TokenInterface) {
                return $this->tokenStorage->setToken($returnValue);
            }

            if ($returnValue instanceof Response) {
                return $event->setResponse($returnValue);
            }
        } catch (AuthenticationException $e) {
            if (null !== $p = $e->getPrevious()) {
                $event->setResponse($p->getHttpResponse());
            }
        }
    }

}
