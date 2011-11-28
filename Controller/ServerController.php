<?php

namespace Alb\OAuth2ServerBundle\Controller;

use OAuth2\OAuth2;
use Symfony\Component\HttpFoundation\Request;
use OAuth2\OAuth2ServerException;

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
            $response = $this->serverService->grantAccessToken($this->request);
            return $response;
        } catch(OAuth2ServerException $e) {
            return $e->getHttpResponse();
        }
    }
}

