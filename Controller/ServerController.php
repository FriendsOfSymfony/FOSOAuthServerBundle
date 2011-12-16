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

use Symfony\Component\HttpFoundation\Request;

class ServerController
{
    protected $request;

    protected $serverService;

    public function __construct(Request $request, OAuth2 $serverService)
    {
        $this->request = $request;
        $this->serverService = $serverService;
    }

    public function tokenAction()
    {
        try {
            return $this->serverService->grantAccessToken($this->request);
        } catch(OAuth2ServerException $e) {
            return $e->getHttpResponse();
        }
    }
}

