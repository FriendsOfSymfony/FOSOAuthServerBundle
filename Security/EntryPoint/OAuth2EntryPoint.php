<?php

namespace Alb\OAuth2ServerBundle\Security\EntryPoint;

use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use OAuth2\OAuth2AuthenticateException;
use OAuth2\OAuth2;

class OAuth2EntryPoint implements AuthenticationEntryPointInterface
{
    protected $serverService;

    public function __construct(OAuth2 $serverService)
    {
        $this->serverService = $serverService;
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        $exception = new OAuth2AuthenticateException(
            OAuth2::HTTP_UNAUTHORIZED
            , OAuth2::TOKEN_TYPE_BEARER
            , $this->serverService->getVariable(OAuth2::CONFIG_WWW_REALM)
            , 'access_denied'
            , 'OAuth2 authentication required'
        );
        
        return $exception->getHttpResponse();
    }
}

