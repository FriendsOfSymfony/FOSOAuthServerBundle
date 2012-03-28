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

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use OAuth2\OAuth2;
use OAuth2\OAuth2ServerException;
use OAuth2\OAuth2RedirectException;

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
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        /* @var $server \OAuth2\OAuth2 */
        $server = $this->container->get('fos_oauth_server.server');
        $form = $this->container->get('fos_oauth_server.authorize.form');
        $formHandler = $this->container->get('fos_oauth_server.authorize.form.handler');

        $process = $formHandler->process();
        if ($process) {
            try {
                $response = $server->finishClientAuthorization($formHandler->isAccepted(), $user, null, null);
                return $response;
            } catch (OAuth2ServerException $e) {
                return $e->getHttpResponse();
            }
        }

        return $this->container->get('templating')->renderResponse(
            'FOSOAuthServerBundle:Authorize:authorize.html.' . $this->container->getParameter('fos_oauth_server.template.engine'),
            array('form' => $form->createView())
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
