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

namespace FOS\OAuthServerBundle\Security\EntryPoint;

use OAuth2\OAuth2;
use OAuth2\OAuth2AuthenticateException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

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
            Response::HTTP_UNAUTHORIZED,
            OAuth2::TOKEN_TYPE_BEARER,
            $this->serverService->getVariable(OAuth2::CONFIG_WWW_REALM),
            'access_denied',
            'OAuth2 authentication required'
        );

        return $exception->getHttpResponse();
    }
}
