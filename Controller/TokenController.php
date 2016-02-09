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

use FOS\OAuthServerBundle\Event\OAuthTokenEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use OAuth2\OAuth2;
use OAuth2\OAuth2ServerException;
use Symfony\Component\HttpFoundation\Response;

class TokenController
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var OAuth2
     */
    protected $server;

    /**
     * @param OAuth2 $server
     * @param ContainerInterface $container
     */
    public function __construct(OAuth2 $server, ContainerInterface $container)
    {
        $this->server = $server;
        $this->container = $container;
    }

    /**
     * @param  Request $request
     * @return Response
     */
    public function tokenAction(Request $request)
    {
        try
        {
            $response = $this->server->grantAccessToken($request);
        }
        catch (OAuth2ServerException $e) {
            return $e->getHttpResponse();
        }

        $data = json_decode($response->getContent(), true);

        $storage = $this->container->get('fos_oauth_server.storage');
        /* @var $storage \FOS\OAuthServerBundle\Storage\OAuthStorage */
        $accessToken = $storage->getAccessToken($data[OAuth2::TOKEN_PARAM_NAME]);
        /* @var $accessToken \FOS\OAuthServerBundle\Entity\AccessToken */

        if($this->container->has('event_dispatcher'))
        {
            $this->container->get('event_dispatcher')->dispatch(
                OAuthTokenEvent::POST_ACCESS_TOKEN_GRANT,
                new OAuthTokenEvent($accessToken)
            );
        }

        return $response;
    }
}
