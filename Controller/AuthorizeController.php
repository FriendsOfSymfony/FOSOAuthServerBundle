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

namespace FOS\OAuthServerBundle\Controller;

use FOS\OAuthServerBundle\Event\PostAuthorizationEvent;
use FOS\OAuthServerBundle\Event\PreAuthorizationEvent;
use FOS\OAuthServerBundle\Form\Handler\AuthorizeFormHandler;
use FOS\OAuthServerBundle\Model\ClientInterface;
use FOS\OAuthServerBundle\Model\ClientManagerInterface;
use OAuth2\OAuth2;
use OAuth2\OAuth2ServerException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;
use Twig\Environment as TwigEnvironment;

/**
 * Controller handling basic authorization.
 *
 * @author Chris Jones <leeked@gmail.com>
 */
class AuthorizeController
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var Form
     */
    private $authorizeForm;

    /**
     * @var AuthorizeFormHandler
     */
    private $authorizeFormHandler;

    /**
     * @var OAuth2
     */
    private $oAuth2Server;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var TwigEnvironment
     */
    private $twig;

    /**
     * @var UrlGeneratorInterface
     */
    private $router;

    /**
     * @var ClientManagerInterface
     */
    private $clientManager;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * This controller had been made as a service due to support symfony 4 where all* services are private by default.
     * Thus, this is considered a bad practice to fetch services directly from container.
     *
     * @todo This controller could be refactored to not rely on so many dependencies
     *
     * @param SessionInterface $session
     */
    public function __construct(
        RequestStack $requestStack,
        Form $authorizeForm,
        AuthorizeFormHandler $authorizeFormHandler,
        OAuth2 $oAuth2Server,
        TokenStorageInterface $tokenStorage,
        UrlGeneratorInterface $router,
        ClientManagerInterface $clientManager,
        EventDispatcherInterface $eventDispatcher,
        TwigEnvironment $twig,
        SessionInterface $session = null
    ) {
        $this->requestStack = $requestStack;
        $this->session = $session;
        $this->authorizeForm = $authorizeForm;
        $this->authorizeFormHandler = $authorizeFormHandler;
        $this->oAuth2Server = $oAuth2Server;
        $this->tokenStorage = $tokenStorage;
        $this->router = $router;
        $this->clientManager = $clientManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->twig = $twig;
    }

    /**
     * Authorize.
     */
    public function authorizeAction(Request $request)
    {
        $user = $this->tokenStorage->getToken()->getUser();

        if (!$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        if ($this->session && true === $this->session->get('_fos_oauth_server.ensure_logout')) {
            $this->session->invalidate(600);
            $this->session->set('_fos_oauth_server.ensure_logout', true);
        }

        $form = $this->authorizeForm;
        $formHandler = $this->authorizeFormHandler;

        /** @var PreAuthorizationEvent $event */
        $event = $this->eventDispatcher->dispatch(new PreAuthorizationEvent($user, $this->getClient()));

        if ($event->isAuthorizedClient()) {
            $scope = $request->get('scope', null);

            return $this->oAuth2Server->finishClientAuthorization(true, $user, $request, $scope);
        }

        if (true === $formHandler->process()) {
            return $this->processSuccess($user, $formHandler, $request);
        }

        return $this->renderAuthorize([
            'form' => $form->createView(),
            'client' => $this->getClient(),
        ]);
    }

    /**
     * @return Response
     */
    protected function processSuccess(UserInterface $user, AuthorizeFormHandler $formHandler, Request $request)
    {
        if ($this->session && true === $this->session->get('_fos_oauth_server.ensure_logout')) {
            $this->tokenStorage->setToken(null);
            $this->session->invalidate();
        }

        $this->eventDispatcher->dispatch(new PostAuthorizationEvent($user, $this->getClient(), $formHandler->isAccepted()));

        $formName = $this->authorizeForm->getName();
        if (!$request->query->all() && $request->request->has($formName)) {
            $request->query->add($request->request->get($formName));
        }

        try {
            return $this->oAuth2Server
                ->finishClientAuthorization($formHandler->isAccepted(), $user, $request, $formHandler->getScope())
            ;
        } catch (OAuth2ServerException $e) {
            return $e->getHttpResponse();
        }
    }

    /**
     * Generate the redirection url when the authorize is completed.
     *
     * @return string
     */
    protected function getRedirectionUrl(UserInterface $user)
    {
        return $this->router->generate('fos_oauth_server_profile_show');
    }

    /**
     * @return ClientInterface
     */
    protected function getClient()
    {
        if (null !== $this->client) {
            return $this->client;
        }

        if (null === $request = $this->getCurrentRequest()) {
            throw new NotFoundHttpException('Client not found.');
        }

        if (null === $clientId = $request->get('client_id')) {
            $formData = $request->get($this->authorizeForm->getName(), []);
            $clientId = isset($formData['client_id']) ? $formData['client_id'] : null;
        }

        $this->client = $this->clientManager->findClientByPublicId($clientId);

        if (null === $this->client) {
            throw new NotFoundHttpException('Client not found.');
        }

        return $this->client;
    }

    protected function renderAuthorize(array $context): Response
    {
        return new Response(
            $this->twig->render('@FOSOAuthServer/Authorize/authorize.html.twig', $context)
        );
    }

    /**
     * @return Request|null
     */
    private function getCurrentRequest()
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            throw new \RuntimeException('No current request.');
        }

        return $request;
    }
}
