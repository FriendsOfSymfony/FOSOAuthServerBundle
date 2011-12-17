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
    protected $serverService;

    public function __construct(OAuth2 $serverService)
    {
        $this->serverService = $serverService;
    }

    public function tokenAction(Request $request)
    {
        try {
            return $this->serverService->grantAccessToken($request);
        } catch(OAuth2ServerException $e) {
            return $e->getHttpResponse();
        }
    }
}

