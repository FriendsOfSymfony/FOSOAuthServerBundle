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
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use OAuth2\OAuth2;
use OAuth2\IOAuth2Storage;
use OAuth2\OAuth2ServerException;
use Symfony\Component\HttpFoundation\Response;

class TokenController
{
    /**
     * @var OAuth2
     */
    protected $server;

    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @param OAuth2 $server
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(OAuth2 $server, EventDispatcherInterface $dispatcher = null)
    {
        $this->server = $server;
        $this->dispatcher = $dispatcher;
    }

     /**
      * @return IOAuth2Storage
      */
     protected function getStorage()
     {
         $reflection = new \ReflectionClass($this->server);
         $storage = $reflection->getProperty('storage');
         $storage->setAccessible(true);

         return $storage->getValue($this->server);
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

        if($this->dispatcher)
        {
            $data = json_decode($response->getContent(), true);

            $accessToken = $this->getStorage()->getAccessToken($data[OAuth2::TOKEN_PARAM_NAME]);

            $event = new OAuthTokenEvent($accessToken);
            $this->dispatcher->dispatch(OAuthTokenEvent::POST_ACCESS_TOKEN_GRANT, $event);
        }

        return $response;
    }
}
