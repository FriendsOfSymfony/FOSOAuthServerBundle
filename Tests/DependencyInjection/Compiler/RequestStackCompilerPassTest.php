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

namespace FOS\OAuthServerBundle\Tests\DependencyInjection\Compiler;

use FOS\OAuthServerBundle\DependencyInjection\Compiler\RequestStackCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class RequestStackCompilerPassTest.
 *
 * @author Nikola Petkanski <nikola@petkanski.com>
 */
class RequestStackCompilerPassTest extends \PHPUnit\Framework\TestCase
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
