<?php

/*
 * This file is part of the FOSOAuthServerBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\OAuthServerBundle\Controller;

use FOS\OAuthServerBundle\Event\OAuthEvent;
use FOS\OAuthServerBundle\Model\ClientInterface;
use FOS\OAuthServerBundle\Model\ClientManagerInterface;
use OAuth2\OAuth2;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Controller handling basic authorization.
 *
 * @author Chris Jones <leeked@gmail.com>
 */
class AuthorizeController
{
    private ?ClientInterface $client;
    private ?SessionInterface $session;
    private Form $authorizeForm;
    private OAuth2 $oAuth2Server;
    private RequestStack $requestStack;
    private TokenStorageInterface $tokenStorage;
    private ClientManagerInterface $clientManager;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        RequestStack $requestStack,
        Form $authorizeForm,
        OAuth2 $oAuth2Server,
        TokenStorageInterface $tokenStorage,
        ClientManagerInterface $clientManager,
        EventDispatcherInterface $eventDispatcher,
        SessionInterface $session = null
    ) {
        $this->requestStack = $requestStack;
        $this->session = $session;
        $this->authorizeForm = $authorizeForm;
        $this->oAuth2Server = $oAuth2Server;
        $this->tokenStorage = $tokenStorage;
        $this->clientManager = $clientManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function authorizeAction(Request $request): Response
    {
        $user = $this->tokenStorage->getToken()->getUser();

        if (!$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        if ($this->session && true === $this->session->get('_fos_oauth_server.ensure_logout')) {
            $this->session->invalidate(600);
            $this->session->set('_fos_oauth_server.ensure_logout', true);
        }

        $event = $this->eventDispatcher->dispatch(new OAuthEvent($user, $this->getClient()));

        $scope = $request->get('scope');

        return $this->oAuth2Server->finishClientAuthorization(true, $user, $request, $scope);
    }

    protected function getClient(): ClientInterface
    {
        if (null !== $this->client) {
            return $this->client;
        }

        if (null === $request = $this->getCurrentRequest()) {
            throw new NotFoundHttpException('Client not found.');
        }

        if (null === $clientId = $request->get('client_id')) {
            $formData = $request->get($this->authorizeForm->getName(), []);
            $clientId = $formData['client_id'] ?? null;
        }

        $this->client = $this->clientManager->findClientByPublicId($clientId);

        if (null === $this->client) {
            throw new NotFoundHttpException('Client not found.');
        }

        return $this->client;
    }

    private function getCurrentRequest(): ?Request
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            throw new \RuntimeException('No current request.');
        }

        return $request;
    }
}
