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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;

class OAuthListenerTest extends TestCase
{
    /**
     * @var OAuth2&\PHPUnit\Framework\MockObject\MockObject
     */
    protected $serverService;

    /**
     * @var AuthenticationManagerInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    protected $authManager;

    /**
     * @var mixed&\PHPUnit\Framework\MockObject\MockObject
     */
    protected $securityContext;

    /**
     * @var RequestEvent&\PHPUnit\Framework\MockObject\MockObject
     */
    protected $event;

    public function setUp(): void
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

        if (interface_exists('Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface')) {
            $this->securityContext = $this
                ->getMockBuilder('Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface')
                ->disableOriginalConstructor()
                ->getMock()
            ;
        } else {
            $this->securityContext = $this->getMockBuilder('Symfony\Component\Security\Core\SecurityContextInterface')
                ->disableOriginalConstructor()
                ->getMock()
            ;
        }

        $this->event = $this
            ->getMockBuilder(RequestEvent::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
    }

    public function testHandle(): void
    {
        $listener = new OAuthListener($this->securityContext, $this->authManager, $this->serverService);

        $this->serverService
            ->expects($this->once())
            ->method('getBearerToken')
            ->will($this->returnValue('a-token'))
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
        $token = $listener($this->event);

        $this->assertInstanceOf(OAuthToken::class, $token);
        $this->assertSame('a-token', $token->getToken());
    }

    public function testHandleResponse(): void
    {
        $listener = new OAuthListener($this->securityContext, $this->authManager, $this->serverService);

        $this->serverService
            ->expects($this->once())
            ->method('getBearerToken')
            ->will($this->returnValue('a-token'))
        ;

        $response = $this->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->authManager
            ->expects($this->once())
            ->method('authenticate')
            ->will($this->returnValue($response))
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

        $ret = $listener($this->event);

        $this->assertSame($response, $ret);
    }
}
