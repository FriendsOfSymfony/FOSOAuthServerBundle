<?php

namespace FOS\OAuthServerBundle\Tests\Controller;

use FOS\OAuthServerBundle\Controller\AuthorizeController;
use FOS\OAuthServerBundle\Event\OAuthEvent;
use FOS\OAuthServerBundle\Form\Handler\AuthorizeFormHandler;
use FOS\OAuthServerBundle\Model\ClientInterface;
use FOS\OAuthServerBundle\Model\ClientManagerInterface;
use OAuth2\OAuth2;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Symfony\Component\EventDispatcher\EventDispatcher;
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

class AuthorizeControllerTest extends TestCase
{
    /**
     * @var \\PHPUnit\Framework\MockObject\MockObject|RequestStack
     */
    protected $requestStack;

    /**
     * @var \\PHPUnit\Framework\MockObject\MockObject|SessionInterface
     */
    protected $session;

    /**
     * @var \\PHPUnit\Framework\MockObject\MockObject|Form
     */
    protected $form;

    /**
     * @var \\PHPUnit\Framework\MockObject\MockObject|AuthorizeFormHandler
     */
    protected $authorizeFormHandler;

    /**
     * @var \\PHPUnit\Framework\MockObject\MockObject|OAuth2
     */
    protected $oAuth2Server;

    /**
     * @var \\PHPUnit\Framework\MockObject\MockObject|EngineInterface
     */
    protected $templateEngine;

    /**
     * @var \\PHPUnit\Framework\MockObject\MockObject|TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var \\PHPUnit\Framework\MockObject\MockObject|UrlGeneratorInterface
     */
    protected $router;

    /**
     * @var \\PHPUnit\Framework\MockObject\MockObject|ClientManagerInterface
     */
    protected $clientManager;

    /**
     * @var \\PHPUnit\Framework\MockObject\MockObject|EventDispatcher
     */
    protected $eventDispatcher;

    /**
     * @var string
     */
    protected $templateEngineType;

    /**
     * @var AuthorizeController
     */
    protected $instance;

    /**
     * @var \\PHPUnit\Framework\MockObject\MockObject|Request
     */
    protected $request;

    /**
     * @var \\PHPUnit\Framework\MockObject\MockObject|UserInterface
     */
    protected $user;

    /**
     * @var \\PHPUnit\Framework\MockObject\MockObject|ClientInterface
     */
    protected $client;

    /**
     * @var \\PHPUnit\Framework\MockObject\MockObject|OAuthEvent
     */
    protected $event;

    /**
     * @var \\PHPUnit\Framework\MockObject\MockObject|FormView
     */
    protected $formView;

    public function setUp(): void
    {
        $this->requestStack = $this->getMockBuilder(RequestStack::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->form = $this->getMockBuilder(Form::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->authorizeFormHandler = $this->getMockBuilder(AuthorizeFormHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->oAuth2Server = $this->getMockBuilder(OAuth2::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->templateEngine = $this->getMockBuilder(Environment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->tokenStorage = $this->getMockBuilder(TokenStorageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->router = $this->getMockBuilder(UrlGeneratorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->clientManager = $this->getMockBuilder(ClientManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventDispatcher = $this->getMockBuilder(EventDispatcher::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->session = $this->getMockBuilder(SessionInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->templateEngineType = 'twig';

        $this->instance = new AuthorizeController(
            $this->requestStack,
            $this->form,
            $this->authorizeFormHandler,
            $this->oAuth2Server,
            $this->templateEngine,
            $this->tokenStorage,
            $this->router,
            $this->clientManager,
            $this->eventDispatcher,
            $this->session,
            $this->templateEngineType
        );

        $this->request = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->request->query = $this->getMockBuilder(ParameterBag::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->request->request = $this->getMockBuilder(ParameterBag::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->user = $this->getMockBuilder(UserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->client = $this->getMockBuilder(ClientInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->event = $this->getMockBuilder(OAuthEvent::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->formView = $this->getMockBuilder(FormView::class)
            ->disableOriginalConstructor()
            ->getMock();

        parent::setUp();
    }

    public function testAuthorizeActionWillThrowAccessDeniedException(): void
    {
        $token = $this->getMockBuilder(TokenInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->tokenStorage
            ->expects(self::at(0))
            ->method('getToken')
            ->willReturn($token);

        $token
            ->expects(self::at(0))
            ->method('getUser')
            ->willReturn(null);

        $this->expectException(AccessDeniedException::class);

        $this->instance->authorizeAction($this->request);
    }

    public function testAuthorizeActionWillRenderTemplate(): void
    {
        $token = $this->getMockBuilder(TokenInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->tokenStorage
            ->expects(self::at(0))
            ->method('getToken')
            ->willReturn($token);

        $token
            ->expects(self::at(0))
            ->method('getUser')
            ->willReturn($this->user);

        $this->session
            ->expects(self::at(0))
            ->method('get')
            ->with('_fos_oauth_server.ensure_logout')
            ->willReturn(false);

        $propertyReflection = new ReflectionProperty(AuthorizeController::class, 'client');
        $propertyReflection->setAccessible(true);
        $propertyReflection->setValue($this->instance, $this->client);

        $this->eventDispatcher
            ->expects(self::at(0))
            ->method('dispatch')
            ->with(OAuthEvent::PRE_AUTHORIZATION_PROCESS, new OAuthEvent($this->user, $this->client))
            ->willReturn($this->event);

        $this->event
            ->expects(self::at(0))
            ->method('isAuthorizedClient')
            ->with()
            ->willReturn(false);

        $this->authorizeFormHandler
            ->expects(self::at(0))
            ->method('process')
            ->with()
            ->willReturn(false);

        $this->form
            ->expects(self::at(0))
            ->method('createView')
            ->willReturn($this->formView);

        $response = new Response();

        $this->templateEngine
            ->expects(self::at(0))
            ->method('render')
            ->with(
                'Authorize/authorize.html.twig',
                [
                    'form' => $this->formView,
                    'client' => $this->client,
                ]
            )
            ->willReturn($response);

        self::assertSame($response, $this->instance->authorizeAction($this->request));
    }

    public function testAuthorizeActionWillFinishClientAuthorization(): void
    {
        $token = $this->getMockBuilder(TokenInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->tokenStorage
            ->expects(self::at(0))
            ->method('getToken')
            ->willReturn($token);

        $token
            ->expects(self::at(0))
            ->method('getUser')
            ->willReturn($this->user);

        $this->session
            ->expects(self::at(0))
            ->method('get')
            ->with('_fos_oauth_server.ensure_logout')
            ->willReturn(false);

        $propertyReflection = new ReflectionProperty(AuthorizeController::class, 'client');
        $propertyReflection->setAccessible(true);
        $propertyReflection->setValue($this->instance, $this->client);

        $this->eventDispatcher
            ->expects(self::at(0))
            ->method('dispatch')
            ->with(OAuthEvent::PRE_AUTHORIZATION_PROCESS, new OAuthEvent($this->user, $this->client))
            ->willReturn($this->event);

        $this->event
            ->expects(self::at(0))
            ->method('isAuthorizedClient')
            ->with()
            ->willReturn(true);

        $randomScope = 'scope'.random_bytes(10);

        $this->request
            ->expects(self::at(0))
            ->method('get')
            ->with('scope', null)
            ->willReturn($randomScope);

        $response = new Response();

        $this->oAuth2Server
            ->expects(self::at(0))
            ->method('finishClientAuthorization')
            ->with(
                true,
                $this->user,
                $this->request,
                $randomScope
            )
            ->willReturn($response);

        self::assertSame($response, $this->instance->authorizeAction($this->request));
    }

    public function testAuthorizeActionWillEnsureLogout(): void
    {
        $token = $this->getMockBuilder(TokenInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->tokenStorage
            ->expects(self::at(0))
            ->method('getToken')
            ->willReturn($token);

        $token
            ->expects(self::at(0))
            ->method('getUser')
            ->willReturn($this->user);

        $this->session
            ->expects(self::at(0))
            ->method('get')
            ->with('_fos_oauth_server.ensure_logout')
            ->willReturn(true);

        $this->session
            ->expects(self::at(1))
            ->method('invalidate')
            ->with(600)
            ->willReturn(true);

        $this->session
            ->expects(self::at(2))
            ->method('set')
            ->with('_fos_oauth_server.ensure_logout', true)
            ->willReturn(null);

        $propertyReflection = new ReflectionProperty(AuthorizeController::class, 'client');
        $propertyReflection->setAccessible(true);
        $propertyReflection->setValue($this->instance, $this->client);

        $this->eventDispatcher
            ->expects(self::at(0))
            ->method('dispatch')
            ->with(OAuthEvent::PRE_AUTHORIZATION_PROCESS, new OAuthEvent($this->user, $this->client))
            ->willReturn($this->event);

        $this->event
            ->expects(self::at(0))
            ->method('isAuthorizedClient')
            ->with()
            ->willReturn(false);

        $this->authorizeFormHandler
            ->expects(self::at(0))
            ->method('process')
            ->with()
            ->willReturn(false);

        $this->form
            ->expects(self::at(0))
            ->method('createView')
            ->willReturn($this->formView);

        $response = new Response();

        $this->templateEngine
            ->expects(self::at(0))
            ->method('render')
            ->with(
                'Authorize/authorize.html.twig',
                [
                    'form' => $this->formView,
                    'client' => $this->client,
                ]
            )
            ->willReturn($response);

        self::assertSame($response, $this->instance->authorizeAction($this->request));
    }

    public function testAuthorizeActionWillProcessAuthorizationForm(): void
    {
        $token = $this->getMockBuilder(TokenInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->tokenStorage
            ->expects(self::once())
            ->method('getToken')
            ->willReturn($token);

        $token
            ->expects(self::once())
            ->method('getUser')
            ->willReturn($this->user);

        $this->session
            ->expects(self::exactly(2))
            ->method('get')
            ->with('_fos_oauth_server.ensure_logout')
            ->willReturn(false);

        $propertyReflection = new ReflectionProperty(AuthorizeController::class, 'client');
        $propertyReflection->setAccessible(true);
        $propertyReflection->setValue($this->instance, $this->client);

        $this->eventDispatcher
            ->expects(self::at(0))
            ->method('dispatch')
            ->with(OAuthEvent::PRE_AUTHORIZATION_PROCESS, new OAuthEvent($this->user, $this->client))
            ->willReturn($this->event);

        $this->event
            ->expects(self::once())
            ->method('isAuthorizedClient')
            ->willReturn(false);

        $this->authorizeFormHandler
            ->expects(self::once())
            ->method('process')
            ->willReturn(true);

        $this->authorizeFormHandler
            ->expects(self::exactly(2))
            ->method('isAccepted')
            ->willReturn(true);

        $this->eventDispatcher
            ->expects(self::at(1))
            ->method('dispatch')
            ->with(
                OAuthEvent::POST_AUTHORIZATION_PROCESS,
                new OAuthEvent($this->user, $this->client, true)
            );

        $formName = 'formName'.random_bytes(10);

        $this->form
            ->expects(self::once())
            ->method('getName')
            ->willReturn($formName);

        $this->request->query
            ->expects(self::once())
            ->method('all')
            ->willReturn([]);

        $this->request->request
            ->expects(self::once())
            ->method('has')
            ->with($formName)
            ->willReturn(false);

        $randomScope = 'scope'.random_bytes(10);

        $this->authorizeFormHandler
            ->expects(self::once())
            ->method('getScope')
            ->willReturn($randomScope);

        $response = new Response();

        $this->oAuth2Server
            ->expects(self::once())
            ->method('finishClientAuthorization')
            ->with(
                true,
                $this->user,
                $this->request,
                $randomScope
            )
            ->willReturn($response);

        self::assertSame($response, $this->instance->authorizeAction($this->request));
    }
}
