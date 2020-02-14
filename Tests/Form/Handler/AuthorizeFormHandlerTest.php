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

namespace FOS\OAuthServerBundle\Tests\Form\Handler;

use FOS\OAuthServerBundle\Form\Handler\AuthorizeFormHandler;
use FOS\OAuthServerBundle\Form\Model\Authorize;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use ReflectionMethod;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class AuthorizeFormHandlerTest extends TestCase
{
    /** @var MockObject | FormInterface */
    protected $form;

    protected $request;

    protected $requestQuery;

    protected $requestRequest;

    /** @var MockObject | ContainerInterface */
    protected $container;

    /**
     * @var AuthorizeFormHandler
     */
    protected $instance;

    public function setUp(): void
    {
        $this->form = $this->getMockBuilder(FormInterface::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
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
        $this->container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->instance = new AuthorizeFormHandler($this->form, $this->request);
        $this->instance->setContainer($this->container);

        $_GET = [];

        parent::setUp();
    }

    public function testConstructWillAcceptRequestObjectAsRequest()
    {
        $request = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->instance = new AuthorizeFormHandler($this->form, $request);

        $this->assertAttributesWereSet($request);
    }

    public function testConstructWillAcceptRequestStackObjectAsRequest()
    {
        $requestStack = $this->getMockBuilder(RequestStack::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->instance = new AuthorizeFormHandler($this->form, $requestStack);

        $this->assertAttributesWereSet($requestStack);
    }

    public function testConstructWillAcceptNullAsRequest()
    {
        $this->instance = new AuthorizeFormHandler($this->form, null);
        $this->assertAttributesWereSet(null);

        $this->instance = new AuthorizeFormHandler($this->form);
        $this->assertAttributesWereSet(null);
    }

    public function testConstructWillThrowException()
    {
        $exceptionMessage = sprintf(
            'Argument 2 of %s must be an instanceof RequestStack or Request',
            AuthorizeFormHandler::class
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($exceptionMessage);

        new AuthorizeFormHandler($this->form, new \stdClass());
    }

    public function testIsAcceptedWillProxyValueToFormData()
    {
        $data = new \stdClass();
        $data->accepted = \random_bytes(10);

        $this->form
            ->expects($this->once())
            ->method('getData')
            ->with()
            ->willReturn($data)
        ;

        self::assertSame($data->accepted, $this->instance->isAccepted());
    }

    public function testIsRejectedWillNegateAcceptedValueFromFormData()
    {
        $dataWithAcceptedValueFalse = new \stdClass();
        $dataWithAcceptedValueFalse->accepted = false;

        $dataWithAcceptedValueTrue = new \stdClass();
        $dataWithAcceptedValueTrue->accepted = true;

        $this->form
            ->expects($this->exactly(2))
            ->method('getData')
            ->willReturnOnConsecutiveCalls(
                $dataWithAcceptedValueFalse,
                $dataWithAcceptedValueTrue
            )
        ;

        self::assertTrue($this->instance->isRejected());
        self::assertFalse($this->instance->isRejected());
    }

    public function testGetScopeWillProxyValueToFormData()
    {
        $data = new \stdClass();
        $data->scope = \random_bytes(10);

        $this->form
            ->expects($this->once())
            ->method('getData')
            ->with()
            ->willReturn($data)
        ;

        self::assertSame($data->scope, $this->instance->getScope());
    }

    public function testGetCurrentRequestWillReturnRequestObject()
    {
        $method = $this->getReflectionMethod('getCurrentRequest');
        self::assertSame($this->request, $method->invoke($this->instance));
    }

    public function testGetCurrentRequestWillReturnCurrentRequestFromRequestStack()
    {
        $requestStack = $this->getMockBuilder(RequestStack::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->instance = new AuthorizeFormHandler($this->form, $requestStack);

        $request = new \stdClass();

        $requestStack
            ->expects($this->once())
            ->method('getCurrentRequest')
            ->with()
            ->willReturn($request)
        ;

        $method = $this->getReflectionMethod('getCurrentRequest');
        self::assertSame($request, $method->invoke($this->instance));
    }

    public function testGetCurrentRequestWillReturnRequestServiceFromContainerIfNoneIsSet()
    {
        $this->instance = new AuthorizeFormHandler($this->form, null);
        $this->instance->setContainer($this->container);

        $randomData = \random_bytes(10);

        $this->container
            ->expects($this->at(0))
            ->method('get')
            ->with('request')
            ->willReturn($randomData)
        ;

        $method = $this->getReflectionMethod('getCurrentRequest');
        self::assertSame($randomData, $method->invoke($this->instance));
    }

    /**
     * @TODO Fix this behavior. This method MUST not modify $_GET.
     */
    public function testOnSuccessWillReplaceGETSuperGlobal()
    {
        $method = $this->getReflectionMethod('onSuccess');

        $data = new \stdClass();
        $data->client_id = \random_bytes(10);
        $data->response_type = \random_bytes(10);
        $data->redirect_uri = \random_bytes(10);
        $data->state = \random_bytes(10);
        $data->scope = \random_bytes(10);

        $this->form
            ->expects($this->exactly(5))
            ->method('getData')
            ->with()
            ->willReturn($data)
        ;

        $_GET = [];

        $expectedSuperGlobalValue = [
            'client_id' => $data->client_id,
            'response_type' => $data->response_type,
            'redirect_uri' => $data->redirect_uri,
            'state' => $data->state,
            'scope' => $data->scope,
        ];

        self::assertNull($method->invoke($this->instance));

        self::assertSame($expectedSuperGlobalValue, $_GET);
    }

    public function testProcessWillReturnFalseIfRequestIsNull()
    {
        $this->instance = new AuthorizeFormHandler($this->form, null);
        $this->instance->setContainer($this->container);

        $this->container
            ->expects($this->once())
            ->method('get')
            ->with('request')
            ->willReturn(null)
        ;

        self::assertFalse($this->instance->process());
    }

    public function testProcessWillSetFormData()
    {
        $this->requestRequest
            ->expects($this->once())
            ->method('has')
            ->with('accepted')
            ->willReturn(true)
        ;

        $dataMock = [
            \random_bytes(10),
            \random_bytes(10),
        ];

        $this->requestQuery
            ->expects($this->once())
            ->method('all')
            ->with()
            ->willReturn($dataMock)
        ;

        $this->form
            ->expects($this->once())
            ->method('setData')
            ->with(new Authorize(
                true,
                $dataMock
            ))
            ->willReturn($this->form)
        ;

        self::assertFalse($this->instance->process());
    }

    public function testProcessWillHandleRequestOnPost()
    {
        $this->requestRequest
            ->expects($this->once())
            ->method('has')
            ->with('accepted')
            ->willReturn(true)
        ;

        $dataMock = [
            \random_bytes(10),
            \random_bytes(10),
        ];

        $this->requestQuery
            ->expects($this->once())
            ->method('all')
            ->with()
            ->willReturn($dataMock)
        ;

        $this->form
            ->expects($this->once())
            ->method('setData')
            ->with(new Authorize(
                true,
                $dataMock
            ))
            ->willReturn($this->form)
        ;

        $this->request
            ->expects($this->once())
            ->method('getMethod')
            ->with()
            ->willReturn('POST')
        ;

        $this->form
            ->expects($this->once())
            ->method('handleRequest')
            ->with($this->request)
            ->willReturn($this->form)
        ;

        $this->form
            ->expects($this->once())
            ->method('isSubmitted')
            ->with()
            ->willReturn(true)
        ;

        $this->form
            ->expects($this->once())
            ->method('isValid')
            ->with()
            ->willReturn(false)
        ;

        self::assertFalse($this->instance->process());
    }

    public function testProcessWillHandleRequestOnPostAndWillProcessDataIfFormIsValid()
    {
        $this->requestRequest
            ->expects($this->once())
            ->method('has')
            ->with('accepted')
            ->willReturn(true)
        ;

        $query = new \stdClass();
        $query->client_id = \random_bytes(10);
        $query->response_type = \random_bytes(10);
        $query->redirect_uri = \random_bytes(10);
        $query->state = \random_bytes(10);
        $query->scope = \random_bytes(10);

        $this->requestQuery
            ->expects($this->once())
            ->method('all')
            ->with()
            ->willReturn((array) $query)
        ;

        $formData = new Authorize(
            true,
            (array) $query
        );

        $this->form
            ->expects($this->once())
            ->method('setData')
            ->with($formData)
            ->willReturn($this->form)
        ;

        $this->request
            ->expects($this->once())
            ->method('getMethod')
            ->with()
            ->willReturn('POST')
        ;

        $this->form
            ->expects($this->once())
            ->method('handleRequest')
            ->with($this->request)
            ->willReturn($this->form)
        ;

        $this->form
            ->expects($this->once())
            ->method('isSubmitted')
            ->with()
            ->willReturn(true)
        ;

        $this->form
            ->expects($this->once())
            ->method('isValid')
            ->with()
            ->willReturn(true)
        ;

        $this->form
            ->expects($this->exactly(5))
            ->method('getData')
            ->with()
            ->willReturn($formData)
        ;

        self::assertSame([], $_GET);

        $expectedSuperGlobalValue = [
            'client_id' => $query->client_id,
            'response_type' => $query->response_type,
            'redirect_uri' => $query->redirect_uri,
            'state' => $query->state,
            'scope' => $query->scope,
        ];

        self::assertTrue($this->instance->process());

        self::assertSame($expectedSuperGlobalValue, $_GET);
    }

    /**
     * @param $methodName
     *
     * @throws ReflectionException
     */
    protected function getReflectionMethod($methodName): ReflectionMethod
    {
        $reflectionObject = new \ReflectionObject($this->instance);
        $reflectionMethod = $reflectionObject->getMethod($methodName);
        $reflectionMethod->setAccessible(true);

        return $reflectionMethod;
    }

    /**
     * @param MockObject $request
     */
    private function assertAttributesWereSet(?MockObject $request): void
    {
        self::assertSame($this->form, $this->instance->getForm());
        self::assertSame($request, $this->instance->getRequest());
    }
}
