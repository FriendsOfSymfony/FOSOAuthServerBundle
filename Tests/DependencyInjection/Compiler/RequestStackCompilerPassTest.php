<?php

namespace FOS\OAuthServerBundle\Tests\DependencyInjection\Compiler;

use FOS\OAuthServerBundle\DependencyInjection\Compiler\RequestStackCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class RequestStackCompilerPassTest
 * @package FOS\OAuthServerBundle\Tests\DependencyInjection\Compiler
 * @author Nikola Petkanski <nikola@petkanski.com>
 */
class RequestStackCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RequestStackCompilerPass
     */
    protected $instance;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ContainerBuilder
     */
    protected $container;

    public function setUp()
    {
        $this->container = $this->getMockBuilder(ContainerBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'has',
                'getDefinition',
            ])
            ->getMock()
        ;

        $this->instance = new RequestStackCompilerPass();

        parent::setUp();
    }

    public function testProcessWithoutRequestStackDoesNothing()
    {
        $this->container
            ->expects($this->once())
            ->method('has')
            ->with('request_stack')
            ->willReturn(true)
        ;

        $this->assertNull($this->instance->process($this->container));
    }

    public function testProcess()
    {
        $this->container
            ->expects($this->once())
            ->method('has')
            ->with('request_stack')
            ->willReturn(false)
        ;

        $definition = $this->getMockBuilder(Definition::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->container
            ->expects($this->once())
            ->method('getDefinition')
            ->with('fos_oauth_server.authorize.form.handler.default')
            ->willReturn($definition)
        ;

        $definition
            ->expects($this->once())
            ->method('addMethodCall')
            ->with(
                'setContainer',
                [
                    new Reference('service_container'),
                ]
            )
            ->willReturn(null)
        ;

        $this->assertNull($this->instance->process($this->container));
    }


}