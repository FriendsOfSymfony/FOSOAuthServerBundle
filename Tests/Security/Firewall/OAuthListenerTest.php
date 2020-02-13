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
    protected $tokenStorage;

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

        $this->tokenStorage = $this
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
        $listener = new OAuthListener($this->tokenStorage, $this->authManager, $this->serverService);

        $this->serverService
            ->expects($this->once())
            ->method('getBearerToken')
            ->willReturn('a-token')
        ;

        $this->authManager
            ->expects($this->once())
            ->method('authenticate')
            ->with($this->isInstanceOf(OAuthToken::class))
            ->will($this->returnArgument(0))
        ;

        $this->tokenStorage
            ->expects($this->once())
            ->method('setToken')
            ->with($this->callback(function($value) {
                return $value instanceof OAuthToken
                    && $value->getToken() === 'a-token'
                ;
            }))
        ;

        // no return, trigger the expectations
        $listener->handle($this->event);
    }

    public function testHandleResponse()
    {
        $listener = new OAuthListener($this->tokenStorage, $this->authManager, $this->serverService);

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
            ->with($this->isInstanceOf(OAuthToken::class))
            ->willReturn($response)
        ;

        $this->tokenStorage
            ->expects($this->never())
            ->method('setToken')
        ;

        $this->event
            ->expects($this->once())
            ->method('setResponse')
            ->with($this->equalTo($response))
        ;

        // no return, trigger the expectations
        $listener->handle($this->event);
    }
}
