<?php

declare(strict_types=1);

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
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * OAuthListener class.
 *
 * @author Arnaud Le Blanc <arnaud.lb@gmail.com>
 */
class OAuthListener
{
    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var AuthenticationManagerInterface
     */
    protected $authenticationManager;

    /**
     * @var OAuth2
     */
    protected $serverService;

    /**
     * @param TokenStorageInterface          $tokenStorage          the token storage
     * @param AuthenticationManagerInterface $authenticationManager the authentication manager
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthenticationManagerInterface $authenticationManager,
        OAuth2 $serverService
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authenticationManager = $authenticationManager;
        $this->serverService = $serverService;
    }

    public function __invoke(RequestEvent $event)
    {
        $this->handle($event);
    }

    public function handle(RequestEvent $event): void
    {
        if (null === $oauthToken = $this->serverService->getBearerToken($event->getRequest(), true)) {
            return;
        }

        $token = new OAuthToken();
        $token->setToken($oauthToken);

        try {
            $authenticateResult = $this->authenticationManager->authenticate($token);

            if ($authenticateResult instanceof TokenInterface) {
                $this->tokenStorage->setToken($authenticateResult);
            } elseif ($authenticateResult instanceof Response) {
                $event->setResponse($authenticateResult);
            }
        } catch (AuthenticationException $e) {
            if (null !== $p = $e->getPrevious()) {
                $event->setResponse($p->getHttpResponse());
            }
        }
    }
}
