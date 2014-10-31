<?php

/*
 * This file is part of the FOSOAuthServerBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\OAuthServerBundle\Security\EntryPoint;

use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpFoundation\Request;
use OAuth2\OAuth2AuthenticateException;
use OAuth2\OAuth2;

class OAuthEntryPoint implements AuthenticationEntryPointInterface
{
    protected $serverService;

    public function __construct(OAuth2 $serverService)
    {
        $this->serverService = $serverService;
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        $exception = new OAuth2AuthenticateException(
            OAuth2::HTTP_UNAUTHORIZED,
            OAuth2::TOKEN_TYPE_BEARER,
            $this->serverService->getVariable(OAuth2::CONFIG_WWW_REALM),
            'access_denied',
            'OAuth2 authentication required'
        );

        return $exception->getHttpResponse();
    }
}
