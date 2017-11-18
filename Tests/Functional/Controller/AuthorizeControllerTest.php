<?php

namespace FOS\OAuthServerBundle\Tests\Functional\Controller;

use FOS\OAuthServerBundle\Controller\AuthorizeController;
use FOS\OAuthServerBundle\Tests\Functional\TestCase;

class AuthorizeControllerTest extends TestCase
{
    public function testAuthorizeControllerIsAccessibleViaController()
    {
        $kernel = static::createKernel(array('env' => 'orm'));
        $kernel->boot();

        $authController = $kernel->getContainer()->get('fos_oauth_server.controller.authorize');

        $this->assertInstanceOf(AuthorizeController::class, $authController);
    }
}
