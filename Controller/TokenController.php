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

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use OAuth2\OAuth2;
use OAuth2\OAuth2ServerException;
use Symfony\Component\HttpFoundation\Response;

class TokenController extends Controller
{
    /**
     * @param Request $request
     *
     * @return Response
     */
    public function tokenAction(Request $request)
    {
        $server = $this->get('fos_oauth_server.server');

        try {
            return $server->grantAccessToken($request);
        } catch (OAuth2ServerException $e) {
            return $e->getHttpResponse();
        }
    }
}
