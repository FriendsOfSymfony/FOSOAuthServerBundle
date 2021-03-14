<?php

/*
 * This file is part of the FOSOAuthServerBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\OAuthServerBundle\Tests\Security\Firewall;

use FOS\OAuthServerBundle\Security\Authentication\Token\OAuthToken;
use FOS\OAuthServerBundle\Security\Firewall\OAuthListener;
use FOS\OAuthServerBundle\Tests\TestCase;
use OAuth2\OAuth2;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class OAuthListenerTest extends TestCase
{
    protected $serverService;

    protected $authManager;

    protected $securityContext;

    protected $event;

    public function setUp(): void
    {
        $this->serverService = $this->getMockBuilder(OAuth2::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->authManager = $this
            ->getMockBuilder(AuthenticationManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        if (interface_exists(TokenStorageInterface::class)) {
            $this->securityContext = $this
                ->getMockBuilder(TokenStorageInterface::class)
                ->disableOriginalConstructor()
                ->getMock();
        } else {
            $this->securityContext = $this->getMockBuilder('Symfony\Component\Security\Core\SecurityContextInterface')
                ->disableOriginalConstructor()
                ->getMock();
        }
    }

    public function testHandle(): void
    {
        $listener = new OAuthListener($this->securityContext, $this->authManager, $this->serverService);

        $this->serverService
            ->expects(self::once())
            ->method('getBearerToken')
            ->willReturn('a-token');

        $this->authManager
            ->expects(self::once())
            ->method('authenticate')
            ->will(self::returnArgument(0));

        $this->securityContext
            ->expects(self::once())
            ->method('setToken')
            ->will(self::returnArgument(0));

        /** @var OAuthToken $token */
        $token = $listener(new ResponseEvent());

        self::assertInstanceOf(OAuthToken::class, $token);
        self::assertEquals('a-token', $token->getToken());
    }

    public function testHandleResponse(): void
    {
        $listener = new OAuthListener($this->securityContext, $this->authManager, $this->serverService);

        $this->serverService
            ->expects(self::once())
            ->method('getBearerToken')
            ->willReturn('a-token');

        $response = $this->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->authManager
            ->expects(self::once())
            ->method('authenticate')
            ->willReturn($response);

        $this->securityContext
            ->expects(self::never())
            ->method('setToken');

        $this->event
            ->expects(self::once())
            ->method('setResponse')
            ->will(self::returnArgument(0));

        $ret = $listener(new ResponseEvent());

        self::assertEquals($response, $ret);
    }
}
