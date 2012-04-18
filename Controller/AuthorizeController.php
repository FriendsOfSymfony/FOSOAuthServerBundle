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
use FOS\OAuthServerBundle\Form\Handler\AuthorizeFormHandler;
use OAuth2\OAuth2;
use OAuth2\OAuth2ServerException;
use OAuth2\OAuth2RedirectException;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Controller handling basic authorization
 *
 * @author Chris Jones <leeked@gmail.com>
 */
class AuthorizeController extends ContainerAware
{
    /**
     * Authorize
     */
    public function authorizeAction(Request $request)
    {
        $user = $this->container->get('security.context')->getToken()->getUser();

        if (!$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        $server = $this->container->get('fos_oauth_server.server');
        $form   = $this->container->get('fos_oauth_server.authorize.form');
        $formHandler = $this->container->get('fos_oauth_server.authorize.form.handler');

        $event = $this->container->get('event_dispatcher')->dispatch(
            OAuthEvent::PRE_AUTHORIZATION_PROCESS,
            new OAuthEvent($user, $this->getClient())
        );

        if ($event->isAuthorizedClient()) {
            $scope = $this->container->get('request')->query->get('scope', null);

            return $server->finishClientAuthorization(true, $user, null, $scope);
        }

        if (true === $formHandler->process()) {
            return $this->processSuccess($user, $formHandler);
        }

        return $this->container->get('templating')->renderResponse(
            'FOSOAuthServerBundle:Authorize:authorize.html.' . $this->container->getParameter('fos_oauth_server.template.engine'),
            array(
                'form'      => $form->createView(),
                'client'    => $this->getClient(),
            )
        );
    }

    /**
     * @param UserInterface $user
     * @param AuthorizeFormHandler $formHandler
     *
     * @return Response
     */
    protected function processSuccess(UserInterface $user, AuthorizeFormHandler $formHandler)
    {
        if (true === $this->container->get('session')->get('_fos_oauth_server.ensure_logout')) {
            $this->container->get('session')->invalidate();
        }

        $this->container->get('dispatcher')->dispatch(
            OAuthEvent::POST_AUTHORIZATION_PROCESS,
            new OAuthEvent($user, $this->getClient(), $formHandler->isAccepted())
        );

        try {
            return $server->finishClientAuthorization($formHandler->isAccepted(), $user, null, $formHandler->getScope());
        } catch (OAuth2ServerException $e) {
            return $e->getHttpResponse();
        }
    }

    /**
     * Generate the redirection url when the authorize is completed
     *
     * @param \FOS\OAuthServerBundle\Model\UserInterface $user
     * @return string
     */
    protected function getRedirectionUrl(UserInterface $user)
    {
        return $this->container->get('router')->generate('fos_oauth_server_profile_show');
    }

    /**
     * @return ClientInterface
     */
    protected function getClient()
    {
        $client = $this->container
            ->get('fos_oauth_server.client_manager')
            ->findClientByPublicId(
                $this->container->get('request')->query->get('client_id')
            );

        if (null === $client) {
            throw new NotFoundHttpException('Client not found.');
        }

        return $client;
    }
}
