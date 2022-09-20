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
use FOS\OAuthServerBundle\Event\PostAuthorizationEvent;
use FOS\OAuthServerBundle\Event\PreAuthorizationEvent;
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
use Twig\Environment as TwigEnvironment;

class AuthorizeControllerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|RequestStack
     */
    protected $requestStack;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|SessionInterface
     */
    protected $session;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|Form
     */
    protected $form;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|AuthorizeFormHandler
     */
    protected $authorizeFormHandler;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|OAuth2
     */
    protected $oAuth2Server;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|TwigEnvironment
     */
    protected $twig;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|UrlGeneratorInterface
     */
    protected $router;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|ClientManagerInterface
     */
    protected $clientManager;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var AuthorizeController
     */
    protected $instance;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|Request
     */
    protected $request;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|ParameterBag
     */
    protected $requestQuery;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|ParameterBag
     */
    protected $requestRequest;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|UserInterface
     */
    protected $user;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|ClientInterface
     */
    protected $client;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|PreAuthorizationEvent
     */
    protected $preAuthorizationEvent;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|PostAuthorizationEvent
     */
    protected $postAuthorizationEvent;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|FormView
     */
    protected $formView;

    public function setUp(): void
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
        $this->tokenStorage = $this->getMockBuilder(TokenStorageInterface::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->twig = $this->getMockBuilder(TwigEnvironment::class)
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
            $this->tokenStorage,
            $this->router,
            $this->clientManager,
            $this->eventDispatcher,
            $this->twig,
            $this->session
        );

        /** @var \PHPUnit\Framework\MockObject\MockObject&Request $request */
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
        $this->preAuthorizationEvent = $this->getMockBuilder(PreAuthorizationEvent::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->postAuthorizationEvent = $this->getMockBuilder(PostAuthorizationEvent::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->formView = $this->getMockBuilder(FormView::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        parent::setUp();
    }

    public function testAuthorizeActionWillThrowAccessDeniedException(): void
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
            ->willReturn(null)
        ;

        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionMessage('This user does not have access to this section.');

        $this->instance->authorizeAction($this->request);
    }

    public function testAuthorizeActionWillRenderTemplate(): void
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
            ->expects($this->once())
            ->method('get')
            ->with('_fos_oauth_server.ensure_logout')
            ->willReturn(false)
        ;

        $propertyReflection = new \ReflectionProperty(AuthorizeController::class, 'client');
        $propertyReflection->setAccessible(true);
        $propertyReflection->setValue($this->instance, $this->client);

        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(new PreAuthorizationEvent($this->user, $this->client))
            ->willReturn($this->preAuthorizationEvent)
        ;

        $this->preAuthorizationEvent
            ->expects($this->once())
            ->method('isAuthorizedClient')
            ->willReturn(false)
        ;

        $this->authorizeFormHandler
            ->expects($this->once())
            ->method('process')
            ->with()
            ->willReturn(false)
        ;

        $this->form
            ->expects($this->once())
            ->method('createView')
            ->willReturn($this->formView)
        ;

        $this->twig
            ->expects($this->once())
            ->method('render')
            ->with(
                '@FOSOAuthServer/Authorize/authorize.html.twig',
                [
                    'form' => $this->formView,
                    'client' => $this->client,
                ]
            )
            ->willReturn($responseBody = 'response')
        ;

        $this->assertSame($responseBody, $this->instance->authorizeAction($this->request)->getContent());
    }

    public function testAuthorizeActionWillFinishClientAuthorization(): void
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
            ->expects($this->once())
            ->method('get')
            ->with('_fos_oauth_server.ensure_logout')
            ->willReturn(false)
        ;

        $propertyReflection = new \ReflectionProperty(AuthorizeController::class, 'client');
        $propertyReflection->setAccessible(true);
        $propertyReflection->setValue($this->instance, $this->client);

        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(new PreAuthorizationEvent($this->user, $this->client))
            ->willReturn($this->preAuthorizationEvent)
        ;

        $this->preAuthorizationEvent
            ->expects($this->once())
            ->method('isAuthorizedClient')
            ->willReturn(true)
        ;

        $randomScope = 'scope'.\random_bytes(10);

        $this->request
            ->expects($this->once())
            ->method('get')
            ->with('scope', null)
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

    public function testAuthorizeActionWillEnsureLogout(): void
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
            ->expects($this->once())
            ->method('get')
            ->with('_fos_oauth_server.ensure_logout')
            ->willReturn(true)
        ;

        $this->session
            ->expects($this->once())
            ->method('invalidate')
            ->with(600)
            ->willReturn(true)
        ;

        $this->session
            ->expects($this->once())
            ->method('set')
            ->with('_fos_oauth_server.ensure_logout', true)
            ->willReturn(null)
        ;

        $propertyReflection = new \ReflectionProperty(AuthorizeController::class, 'client');
        $propertyReflection->setAccessible(true);
        $propertyReflection->setValue($this->instance, $this->client);

        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(new PreAuthorizationEvent($this->user, $this->client))
            ->willReturn($this->preAuthorizationEvent)
        ;

        $this->preAuthorizationEvent
            ->expects($this->once())
            ->method('isAuthorizedClient')
            ->willReturn(false)
        ;

        $this->authorizeFormHandler
            ->expects($this->once())
            ->method('process')
            ->with()
            ->willReturn(false)
        ;

        $this->form
            ->expects($this->once())
            ->method('createView')
            ->willReturn($this->formView)
        ;

        $this->twig
            ->expects($this->once())
            ->method('render')
            ->with(
                '@FOSOAuthServer/Authorize/authorize.html.twig',
                [
                    'form' => $this->formView,
                    'client' => $this->client,
                ]
            )
            ->willReturn($responseBody = 'response')
        ;

        $this->assertSame($responseBody, $this->instance->authorizeAction($this->request)->getContent());
    }

    public function testAuthorizeActionWillProcessAuthorizationForm(): void
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

        $this->preAuthorizationEvent
            ->expects($this->once())
            ->method('isAuthorizedClient')
            ->willReturn(false)
        ;
        $postAuthorizationEvent = new PostAuthorizationEvent($this->user, $this->client, true);

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
            ->expects($this->exactly(2))
            ->method('dispatch')
            ->withConsecutive(
                [$this->equalTo(new PreAuthorizationEvent($this->user, $this->client))],
                [$this->equalTo($postAuthorizationEvent)]
            )
            ->willReturn(
                $this->preAuthorizationEvent,
                $postAuthorizationEvent
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
