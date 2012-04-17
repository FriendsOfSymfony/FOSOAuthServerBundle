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

        if ($process = $formHandler->process()) {
            try {
                return $server->finishClientAuthorization($formHandler->isAccepted(), $user, null, null);
            } catch (OAuth2ServerException $e) {
                return $e->getHttpResponse();
            }
        }

        $client = $this->container
            ->get('fos_oauth_server.client_manager')
            ->findClientByPublicId(
                $this->container->get('request')->query->get('client_id')
            );

        if (null === $client) {
            throw new NotFoundHttpException('No client found.');
        }

        return $this->container->get('templating')->renderResponse(
            'FOSOAuthServerBundle:Authorize:authorize.html.' . $this->container->getParameter('fos_oauth_server.template.engine'),
            array(
                'form'      => $form->createView(),
                'client'    => $client,
            )
        );
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
}
