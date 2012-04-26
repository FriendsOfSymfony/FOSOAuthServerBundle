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

use FOS\OAuthServerBundle\Security\Firewall\OAuthListener;
use FOS\OAuthServerBundle\Tests\TestCase;

class OAuthListenerTest extends TestCase
{
    protected $serverService;

    protected $authManager;

    protected $securityContext;

    protected $event;

    public function setUp()
    {
        $this->serverService = $this
            ->getMockBuilder('OAuth2\OAuth2')
            ->disableOriginalConstructor()
            ->getMock();

        $this->authManager = $this
            ->getMock('Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface');

        $this->securityContext = $this
            ->getMock('Symfony\Component\Security\Core\SecurityContextInterface');

        $this->event = $this
            ->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testHandle()
    {
        $listener = new OAuthListener($this->securityContext, $this->authManager, $this->serverService);

        $this->serverService
            ->expects($this->once())
            ->method('getBearerToken')
            ->will($this->returnValue('a-token'));

        $this->authManager
            ->expects($this->once())
            ->method('authenticate')
            ->will($this->returnArgument(0));

        $this->securityContext
            ->expects($this->once())
            ->method('setToken')
            ->will($this->returnArgument(0));

        $token = $listener->handle($this->event);

        $this->assertInstanceOf('FOS\OAuthServerBundle\Security\Authentication\Token\OAuthToken', $token);
        $this->assertEquals('a-token', $token->getToken());
    }

    public function testHandleResponse()
    {
        $listener = new OAuthListener($this->securityContext, $this->authManager, $this->serverService);

        $this->serverService
            ->expects($this->once())
            ->method('getBearerToken')
            ->will($this->returnValue('a-token'));

        $response = $this->getMock('Symfony\Component\HttpFoundation\Response');

        $this->authManager
            ->expects($this->once())
            ->method('authenticate')
            ->will($this->returnValue($response));

        $this->securityContext
            ->expects($this->never())
            ->method('setToken');

        $this->event
            ->expects($this->once())
            ->method('setResponse')
            ->will($this->returnArgument(0));

        $ret = $listener->handle($this->event);

        $this->assertEquals($response, $ret);
    }

}
