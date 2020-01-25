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

namespace FOS\OAuthServerBundle\Tests\Security\Firewall;

use FOS\OAuthServerBundle\Security\Authentication\Token\OAuthToken;
use FOS\OAuthServerBundle\Security\Firewall\OAuthListener;
use FOS\OAuthServerBundle\Tests\TestCase;
use OAuth2\OAuth2;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class OAuthListenerTest extends TestCase
{
    /** @var MockObject | OAuth2 */
    protected $serverService;

    /** @var MockObject | AuthenticationManagerInterface */
    protected $authManager;

    /** @var MockObject | TokenStorageInterface */
    protected $securityContext;

    /** @var MockObject | RequestEvent */
    protected $event;

    public function setUp() : void
    {
        $this->serverService = $this->getMockBuilder(OAuth2::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->authManager = $this
            ->getMockBuilder(AuthenticationManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->securityContext = $this
            ->getMockBuilder(TokenStorageInterface::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->event = $this
            ->getMockBuilder(RequestEvent::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
    }

    public function testHandle()
    {
        $listener = new OAuthListener($this->securityContext, $this->authManager, $this->serverService);

        $this->serverService
            ->expects($this->once())
            ->method('getBearerToken')
            ->willReturn('a-token')
        ;

        $this->authManager
            ->expects($this->once())
            ->method('authenticate')
            ->will($this->returnArgument(0))
        ;

        $this->securityContext
            ->expects($this->once())
            ->method('setToken')
            ->will($this->returnArgument(0))
        ;

        /** @var OAuthToken $token */
        $token = $listener->handle($this->event);

        self::assertInstanceOf(OAuthToken::class, $token);
        self::assertSame('a-token', $token->getToken());
    }

    public function testHandleResponse()
    {
        $listener = new OAuthListener($this->securityContext, $this->authManager, $this->serverService);

        $this->serverService
            ->expects($this->once())
            ->method('getBearerToken')
            ->willReturn('a-token')
        ;

        $response = $this->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->authManager
            ->expects($this->once())
            ->method('authenticate')
            ->willReturn($response)
        ;

        $this->securityContext
            ->expects($this->never())
            ->method('setToken')
        ;

        $this->event
            ->expects($this->once())
            ->method('setResponse')
            ->will($this->returnArgument(0))
        ;

        $ret = $listener->handle($this->event);

        self::assertSame($response, $ret);
    }
}
