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

use FOS\OAuthServerBundle\Controller\AuthorizeController;
use FOS\OAuthServerBundle\Event\OAuthEvent;
use FOS\OAuthServerBundle\Form\Handler\AuthorizeFormHandler;
use FOS\OAuthServerBundle\Model\ClientInterface;
use FOS\OAuthServerBundle\Model\ClientManagerInterface;
use OAuth2\OAuth2;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;
use Twig\Environment;

class AuthorizeControllerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|RequestStack
     */
    protected $requestStack;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|SessionInterface
     */
    protected $session;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Form
     */
    protected $form;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|AuthorizeFormHandler
     */
    protected $authorizeFormHandler;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|OAuth2
     */
    protected $oAuth2Server;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Environment
     */
    protected $twig;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|UrlGeneratorInterface
     */
    protected $router;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ClientManagerInterface
     */
    protected $clientManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var AuthorizeController
     */
    protected $instance;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Request
     */
    protected $request;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ParameterBag
     */
    protected $requestQuery;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ParameterBag
     */
    protected $requestRequest;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|UserInterface
     */
    protected $user;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ClientInterface
     */
    protected $client;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|OAuthEvent
     */
    protected $event;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|FormView
     */
    protected $formView;

    public function setUp()
    {
        $this->requestStack = $this->getMockBuilder(RequestStack::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->form = $this->getMockBuilder(Form::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->authorizeFormHandler = $this->getMockBuilder(AuthorizeFormHandler::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->oAuth2Server = $this->getMockBuilder(OAuth2::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->twig = $this->getMockBuilder(Environment::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->tokenStorage = $this->getMockBuilder(TokenStorageInterface::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->router = $this->getMockBuilder(UrlGeneratorInterface::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->clientManager = $this->getMockBuilder(ClientManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->eventDispatcher = $this->getMockBuilder(EventDispatcherInterface::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->session = $this->getMockBuilder(SessionInterface::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->instance = new AuthorizeController(
            $this->requestStack,
            $this->form,
            $this->authorizeFormHandler,
            $this->oAuth2Server,
            $this->twig,
            $this->tokenStorage,
            $this->router,
            $this->clientManager,
            $this->eventDispatcher,
            $this->session
        );

        /** @var \PHPUnit_Framework_MockObject_MockObject&Request $request */
        $request = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->requestQuery = $this->getMockBuilder(ParameterBag::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->requestRequest = $this->getMockBuilder(ParameterBag::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $request->query = $this->requestQuery;
        $request->request = $this->requestRequest;
        $this->request = $request;
        $this->user = $this->getMockBuilder(UserInterface::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->client = $this->getMockBuilder(ClientInterface::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->event = $this->getMockBuilder(OAuthEvent::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->formView = $this->getMockBuilder(FormView::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        parent::setUp();
    }

    public function testAuthorizeActionWillThrowAccessDeniedException()
    {
        $token = $this->getMockBuilder(TokenInterface::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->tokenStorage
            ->expects($this->at(0))
            ->method('getToken')
            ->willReturn($token)
        ;

        $token
            ->expects($this->at(0))
            ->method('getUser')
            ->willReturn(null)
        ;

        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionMessage('This user does not have access to this section.');

        $this->instance->authorizeAction($this->request);
    }

    public function testAuthorizeActionWillRenderTemplate()
    {
        $token = $this->getMockBuilder(TokenInterface::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->tokenStorage
            ->expects($this->at(0))
            ->method('getToken')
            ->willReturn($token)
        ;

        $token
            ->expects($this->at(0))
            ->method('getUser')
            ->willReturn($this->user)
        ;

        $this->session
            ->expects($this->at(0))
            ->method('get')
            ->with('_fos_oauth_server.ensure_logout')
            ->willReturn(false)
        ;

        $propertyReflection = new \ReflectionProperty(AuthorizeController::class, 'client');
        $propertyReflection->setAccessible(true);
        $propertyReflection->setValue($this->instance, $this->client);

        $this->eventDispatcher
            ->expects($this->at(0))
            ->method('dispatch')
            ->with(OAuthEvent::PRE_AUTHORIZATION_PROCESS, new OAuthEvent($this->user, $this->client))
            ->willReturn($this->event)
        ;

        $this->event
            ->expects($this->at(0))
            ->method('isAuthorizedClient')
            ->with()
            ->willReturn(false)
        ;

        $this->authorizeFormHandler
            ->expects($this->at(0))
            ->method('process')
            ->with()
            ->willReturn(false)
        ;

        $this->form
            ->expects($this->at(0))
            ->method('createView')
            ->willReturn($this->formView)
        ;

        $response = new Response();

        $this->twig
            ->expects($this->at(0))
            ->method('render')
            ->with(
                '@FOSOAuthServer/Authorize/authorize.html.twig',
                [
                    'form' => $this->formView,
                    'client' => $this->client,
                ]
            )
            ->willReturn($response)
        ;

        $this->assertSame($response, $this->instance->authorizeAction($this->request));
    }

    public function testAuthorizeActionWillFinishClientAuthorization()
    {
        $token = $this->getMockBuilder(TokenInterface::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->tokenStorage
            ->expects($this->at(0))
            ->method('getToken')
            ->willReturn($token)
        ;

        $token
            ->expects($this->at(0))
            ->method('getUser')
            ->willReturn($this->user)
        ;

        $this->session
            ->expects($this->at(0))
            ->method('get')
            ->with('_fos_oauth_server.ensure_logout')
            ->willReturn(false)
        ;

        $propertyReflection = new \ReflectionProperty(AuthorizeController::class, 'client');
        $propertyReflection->setAccessible(true);
        $propertyReflection->setValue($this->instance, $this->client);

        $this->eventDispatcher
            ->expects($this->at(0))
            ->method('dispatch')
            ->with(OAuthEvent::PRE_AUTHORIZATION_PROCESS, new OAuthEvent($this->user, $this->client))
            ->willReturn($this->event)
        ;

        $this->event
            ->expects($this->at(0))
            ->method('isAuthorizedClient')
            ->with()
            ->willReturn(true)
        ;

        $randomScope = 'scope'.\random_bytes(10);

        $this->request
            ->expects($this->at(0))
            ->method('get')
            ->with('scope', null)
            ->willReturn($randomScope)
        ;

        $response = new Response();

        $this->oAuth2Server
            ->expects($this->at(0))
            ->method('finishClientAuthorization')
            ->with(
                true,
                $this->user,
                $this->request,
                $randomScope
            )
            ->willReturn($response)
        ;

        $this->assertSame($response, $this->instance->authorizeAction($this->request));
    }

    public function testAuthorizeActionWillEnsureLogout()
    {
        $token = $this->getMockBuilder(TokenInterface::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->tokenStorage
            ->expects($this->at(0))
            ->method('getToken')
            ->willReturn($token)
        ;

        $token
            ->expects($this->at(0))
            ->method('getUser')
            ->willReturn($this->user)
        ;

        $this->session
            ->expects($this->at(0))
            ->method('get')
            ->with('_fos_oauth_server.ensure_logout')
            ->willReturn(true)
        ;

        $this->session
            ->expects($this->at(1))
            ->method('invalidate')
            ->with(600)
            ->willReturn(true)
        ;

        $this->session
            ->expects($this->at(2))
            ->method('set')
            ->with('_fos_oauth_server.ensure_logout', true)
            ->willReturn(null)
        ;

        $propertyReflection = new \ReflectionProperty(AuthorizeController::class, 'client');
        $propertyReflection->setAccessible(true);
        $propertyReflection->setValue($this->instance, $this->client);

        $this->eventDispatcher
            ->expects($this->at(0))
            ->method('dispatch')
            ->with(OAuthEvent::PRE_AUTHORIZATION_PROCESS, new OAuthEvent($this->user, $this->client))
            ->willReturn($this->event)
        ;

        $this->event
            ->expects($this->at(0))
            ->method('isAuthorizedClient')
            ->with()
            ->willReturn(false)
        ;

        $this->authorizeFormHandler
            ->expects($this->at(0))
            ->method('process')
            ->with()
            ->willReturn(false)
        ;

        $this->form
            ->expects($this->at(0))
            ->method('createView')
            ->willReturn($this->formView)
        ;

        $response = new Response();

        $this->twig
            ->expects($this->at(0))
            ->method('render')
            ->with(
                '@FOSOAuthServer/Authorize/authorize.html.twig',
                [
                    'form' => $this->formView,
                    'client' => $this->client,
                ]
            )
            ->willReturn($response)
        ;

        $this->assertSame($response, $this->instance->authorizeAction($this->request));
    }

    public function testAuthorizeActionWillProcessAuthorizationForm()
    {
        $token = $this->getMockBuilder(TokenInterface::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->tokenStorage
            ->expects($this->once())
            ->method('getToken')
            ->willReturn($token)
        ;

        $token
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($this->user)
        ;

        $this->session
            ->expects($this->exactly(2))
            ->method('get')
            ->with('_fos_oauth_server.ensure_logout')
            ->willReturn(false)
        ;

        $propertyReflection = new \ReflectionProperty(AuthorizeController::class, 'client');
        $propertyReflection->setAccessible(true);
        $propertyReflection->setValue($this->instance, $this->client);

        $this->eventDispatcher
            ->expects($this->at(0))
            ->method('dispatch')
            ->with(OAuthEvent::PRE_AUTHORIZATION_PROCESS, new OAuthEvent($this->user, $this->client))
            ->willReturn($this->event)
        ;

        $this->event
            ->expects($this->once())
            ->method('isAuthorizedClient')
            ->willReturn(false)
        ;

        $this->authorizeFormHandler
            ->expects($this->once())
            ->method('process')
            ->willReturn(true)
        ;

        $this->authorizeFormHandler
            ->expects($this->exactly(2))
            ->method('isAccepted')
            ->willReturn(true)
        ;

        $this->eventDispatcher
            ->expects($this->at(1))
            ->method('dispatch')
            ->with(
                OAuthEvent::POST_AUTHORIZATION_PROCESS,
                new OAuthEvent($this->user, $this->client, true)
            )
        ;

        $formName = 'formName'.\random_bytes(10);

        $this->form
            ->expects($this->once())
            ->method('getName')
            ->willReturn($formName)
        ;

        $this->requestQuery
            ->expects($this->once())
            ->method('all')
            ->willReturn([])
        ;

        $this->requestRequest
            ->expects($this->once())
            ->method('has')
            ->with($formName)
            ->willReturn(false)
        ;

        $randomScope = 'scope'.\random_bytes(10);

        $this->authorizeFormHandler
            ->expects($this->once())
            ->method('getScope')
            ->willReturn($randomScope)
        ;

        $response = new Response();

        $this->oAuth2Server
            ->expects($this->once())
            ->method('finishClientAuthorization')
            ->with(
                true,
                $this->user,
                $this->request,
                $randomScope
            )
            ->willReturn($response)
        ;

        $this->assertSame($response, $this->instance->authorizeAction($this->request));
    }
}
