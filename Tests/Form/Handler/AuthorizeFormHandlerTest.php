<?php /** @noinspection GlobalVariableUsageInspection */

namespace FOS\OAuthServerBundle\Tests\Form\Handler;

use FOS\OAuthServerBundle\Form\Handler\AuthorizeFormHandler;
use FOS\OAuthServerBundle\Form\Model\Authorize;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use ReflectionObject;
use stdClass;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class AuthorizeFormHandlerTest
 * @package FOS\OAuthServerBundle\Tests\Form\Handler
 * @author Nikola Petkanski <nikola@petkanski.com>
 */
class AuthorizeFormHandlerTest extends TestCase
{
    /**
     * @var \\PHPUnit\Framework\MockObject\MockObject|FormInterface
     */
    protected $form;

    /**
     * @var \\PHPUnit\Framework\MockObject\MockObject|Request|RequestStack
     */
    protected $request;

    /**
     * @var \\PHPUnit\Framework\MockObject\MockObject|ParameterBag
     */
    protected $requestQuery;

    /**
     * @var \\PHPUnit\Framework\MockObject\MockObject|ParameterBag
     */
    protected $requestRequest;

    /**
     * @var \\PHPUnit\Framework\MockObject\MockObject|ContainerInterface
     */
    protected $container;

    /**
     * @var AuthorizeFormHandler
     */
    protected $instance;

    public function setUp(): void
    {
        $this->form = $this->getMockBuilder(FormInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->request = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestQuery = $this->getMockBuilder(ParameterBag::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestRequest = $this->getMockBuilder(ParameterBag::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->request->query = $this->requestQuery;
        $this->request->request = $this->requestRequest;
        $this->container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->instance = new AuthorizeFormHandler($this->form, $this->request);
        $this->instance->setContainer($this->container);

        parent::setUp();
    }

    public function testConstructWillAcceptRequestObjectAsRequest(): void
    {
        $request = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->instance = new AuthorizeFormHandler($this->form, $request);
    }

    public function testConstructWillAcceptRequestStackObjectAsRequest(): void
    {
        $requestStack = $this->getMockBuilder(RequestStack::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->instance = new AuthorizeFormHandler($this->form, $requestStack);
    }

    public function testConstructWillThrowException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new AuthorizeFormHandler($this->form, new stdClass());
    }

    public function testIsAcceptedWillProxyValueToFormData(): void
    {
        $data = new stdClass();
        $data->accepted = random_bytes(10);

        $this->form
            ->expects(self::once())
            ->method('getData')
            ->with()
            ->willReturn($data);

        self::assertSame($data->accepted, $this->instance->isAccepted());
    }

    public function testIsRejectedWillNegateAcceptedValueFromFormData(): void
    {
        $dataWithAcceptedValueFalse = new stdClass();
        $dataWithAcceptedValueFalse->accepted = false;

        $dataWithAcceptedValueTrue = new stdClass();
        $dataWithAcceptedValueTrue->accepted = true;

        $this->form
            ->expects(self::exactly(2))
            ->method('getData')
            ->willReturnOnConsecutiveCalls(
                $dataWithAcceptedValueFalse,
                $dataWithAcceptedValueTrue
            );

        self::assertTrue($this->instance->isRejected());
        self::assertFalse($this->instance->isRejected());
    }

    public function testGetScopeWillProxyValueToFormData(): void
    {
        $data = new stdClass();
        $data->scope = random_bytes(10);

        $this->form
            ->expects(self::once())
            ->method('getData')
            ->with()
            ->willReturn($data);

        self::assertSame($data->scope, $this->instance->getScope());
    }

    public function testGetCurrentRequestWillReturnRequestObject(): void
    {
        $method = $this->getReflectionMethod('getCurrentRequest');
        self::assertSame($this->request, $method->invoke($this->instance));
    }

    public function testGetCurrentRequestWillReturnCurrentRequestFromRequestStack(): void
    {
        $requestStack = $this->getMockBuilder(RequestStack::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->instance = new AuthorizeFormHandler($this->form, $requestStack);

        $request = new stdClass();

        $requestStack
            ->expects(self::once())
            ->method('getCurrentRequest')
            ->with()
            ->willReturn($request);

        $method = $this->getReflectionMethod('getCurrentRequest');
        self::assertSame($request, $method->invoke($this->instance));
    }

    public function testGetCurrentRequestWillReturnRequestServiceFromContainerIfNoneIsSet(): void
    {
        $this->instance = new AuthorizeFormHandler($this->form, null);
        $this->instance->setContainer($this->container);

        $randomData = random_bytes(10);

        $this->container
            ->expects(self::at(0))
            ->method('get')
            ->with('request')
            ->willReturn($randomData);

        $method = $this->getReflectionMethod('getCurrentRequest');
        self::assertSame($randomData, $method->invoke($this->instance));
    }

    /**
     * @group legacy
     * @expectedDeprecation FOS\OAuthServerBundle\Form\Handler\AuthorizeFormHandler::$request is deprecated since 1.4 and will be removed in 2.0.
     */
    public function testGettingRequestVariableWillWorkButWillTriggerUserWarning(): void
    {
        self::assertSame($this->request, $this->instance->__get('request'));
    }

    public function testGettingAnyVariableWillReturnNull(): void
    {
        self::assertNull($this->instance->__get('test'));
    }

    /**
     * @TODO Fix this behavior. This method MUST not modify $_GET.
     */
    public function testOnSuccessWillReplaceGETSuperGlobal(): void
    {
        $method = $this->getReflectionMethod('onSuccess');

        $data = new stdClass();
        $data->client_id = random_bytes(10);
        $data->response_type = random_bytes(10);
        $data->redirect_uri = random_bytes(10);
        $data->state = random_bytes(10);
        $data->scope = random_bytes(10);

        $this->form
            ->expects(self::exactly(5))
            ->method('getData')
            ->with()
            ->willReturn($data);

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

    public function testProcessWillReturnFalseIfRequestIsNull(): void
    {
        $this->instance = new AuthorizeFormHandler($this->form, null);
        $this->instance->setContainer($this->container);

        $this->container
            ->expects(self::once())
            ->method('get')
            ->with('request')
            ->willReturn(null);

        self::assertFalse($this->instance->process());
    }

    public function testProcessWillSetFormData(): void
    {
        $this->requestRequest
            ->expects(self::once())
            ->method('has')
            ->with('accepted')
            ->willReturn(true);

        $dataMock = [
            random_bytes(10),
            random_bytes(10),
        ];

        $this->requestQuery
            ->expects(self::once())
            ->method('all')
            ->with()
            ->willReturn($dataMock);

        $this->form
            ->expects(self::once())
            ->method('setData')
            ->with(
                new Authorize(
                    true,
                    $dataMock
                )
            )
            ->willReturn($this->form);

        self::assertFalse($this->instance->process());
    }

    public function testProcessWillHandleRequestOnPost(): void
    {
        $this->requestRequest
            ->expects(self::once())
            ->method('has')
            ->with('accepted')
            ->willReturn(true);

        $dataMock = [
            random_bytes(10),
            random_bytes(10),
        ];

        $this->requestQuery
            ->expects(self::once())
            ->method('all')
            ->with()
            ->willReturn($dataMock);

        $this->form
            ->expects(self::once())
            ->method('setData')
            ->with(
                new Authorize(
                    true,
                    $dataMock
                )
            )
            ->willReturn($this->form);

        $this->request
            ->expects(self::once())
            ->method('getMethod')
            ->with()
            ->willReturn('POST');

        $this->form
            ->expects(self::once())
            ->method('handleRequest')
            ->with($this->request)
            ->willReturn($this->form);

        $this->form
            ->expects(self::once())
            ->method('isSubmitted')
            ->with()
            ->willReturn(true);

        $this->form
            ->expects(self::once())
            ->method('isValid')
            ->with()
            ->willReturn(false);

        self::assertFalse($this->instance->process());
    }

    public function testProcessWillHandleRequestOnPostAndWillProcessDataIfFormIsValid(): void
    {
        $this->requestRequest
            ->expects(self::once())
            ->method('has')
            ->with('accepted')
            ->willReturn(true);

        $query = new stdClass();
        $query->client_id = random_bytes(10);
        $query->response_type = random_bytes(10);
        $query->redirect_uri = random_bytes(10);
        $query->state = random_bytes(10);
        $query->scope = random_bytes(10);

        $this->requestQuery
            ->expects(self::once())
            ->method('all')
            ->with()
            ->willReturn((array)$query);

        $formData = new Authorize(
            true,
            (array)$query
        );

        $this->form
            ->expects(self::once())
            ->method('setData')
            ->with($formData)
            ->willReturn($this->form);

        $this->request
            ->expects(self::once())
            ->method('getMethod')
            ->with()
            ->willReturn('POST');

        $this->form
            ->expects(self::once())
            ->method('handleRequest')
            ->with($this->request)
            ->willReturn($this->form);

        $this->form
            ->expects(self::once())
            ->method('isSubmitted')
            ->with()
            ->willReturn(true);

        $this->form
            ->expects(self::once())
            ->method('isValid')
            ->with()
            ->willReturn(true);

        $this->form
            ->expects(self::exactly(5))
            ->method('getData')
            ->with()
            ->willReturn($formData);

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

    protected function getReflectionMethod(string $methodName): ReflectionMethod
    {
        $reflectionObject = new ReflectionObject($this->instance);
        $reflectionMethod = $reflectionObject->getMethod($methodName);
        $reflectionMethod->setAccessible(true);

        return $reflectionMethod;
    }
}
