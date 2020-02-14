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

namespace FOS\OAuthServerBundle\Tests\Controller;

use FOS\OAuthServerBundle\Tests\Functional\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;

class AuthorizeControllerFunctionalTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->client = $this->createClient();
    }

    public function tearDown(): void
    {
        unset($this->client);

        parent::tearDown();
    }

    public function testAuthorizeActionWillThrowAccessDeniedException(): void
    {
        self::$kernel->getContainer()->get('security.token_storage')->setToken(new AnonymousToken('test-secret', 'anon'));

        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionMessage('This user does not have access to this section.');

        $this->client->catchExceptions(false);
        $this->client->request('GET', '/oauth/v2/auth');
    }

    public function testAuthorizeActionWillRenderTemplate(): void
    {
        $user = $this->getMockBuilder(UserInterface::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        self::$kernel->getContainer()->get('security.token_storage')->setToken(
            new PostAuthenticationGuardToken($user, 'member_area', ['ROLE_USER'])
        );

        $this->client->catchExceptions(false);
        $this->client->request('GET', '/oauth/v2/auth', [
            'client_id' => '123_test-client-id',
        ]);

        $this->assertResponse(200, '<form name="fos_oauth_server_authorize_form" method="post" action="/oauth/v2/auth">');
    }

    public function testAuthorizeActionWillFinishClientAuthorization(): void
    {
        // TODO: refactor unit AuthorizeControllerTest as functional test here
        $this->assertTrue(true);
    }

    public function testAuthorizeActionWillEnsureLogout(): void
    {
        // TODO: refactor unit AuthorizeControllerTest as functional test here
        $this->assertTrue(true);
    }

    public function testAuthorizeActionWillProcessAuthorizationForm(): void
    {
        // TODO: refactor unit AuthorizeControllerTest as functional test here
        $this->assertTrue(true);
    }
}
