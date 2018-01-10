<?php

namespace FOS\OAuthServerBundle\Tests\DependencyInjection\Compiler;

use FOS\OAuthServerBundle\DependencyInjection\Compiler\TokenStorageCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class TokenStorageCompilerPassTest
 * @package FOS\OAuthServerBundle\Tests\DependencyInjection\Compiler
 * @author Nikola Petkanski <nikola@petkanski.com>
 */
class TokenStorageCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TokenStorageCompilerPass
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
                'getDefinition',
                'hasDefinition',
            ])
            ->getMock()
        ;
        $this->instance = new TokenStorageCompilerPass();

        parent::setUp();
    }

    public function testProcessWithExistingTokenStorage()
    {
        $authenticationListenerDefinition = $this->getMockBuilder(Definition::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->container
            ->expects($this->once())
            ->method('getDefinition')
            ->with('fos_oauth_server.security.authentication.listener')
            ->willReturn($authenticationListenerDefinition)
        ;

        $this->container
            ->expects($this->once())
            ->method('hasDefinition')
            ->with('security.token_storage')
            ->willReturn(true)
        ;

        $this->assertNull($this->instance->process($this->container));
    }

    public function testProcessWithoutExistingTokenStorage()
    {
        $authenticationListenerDefinition = $this->getMockBuilder(Definition::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->container
            ->expects($this->once())
            ->method('getDefinition')
            ->with('fos_oauth_server.security.authentication.listener')
            ->willReturn($authenticationListenerDefinition)
        ;

        $this->container
            ->expects($this->once())
            ->method('hasDefinition')
            ->with('security.token_storage')
            ->willReturn(false)
        ;

        $authenticationListenerDefinition
            ->expects($this->once())
            ->method('replaceArgument')
            ->with(
                0,
                new Reference('security.context')
            )
            ->willReturn(null)
        ;

        $this->assertNull($this->instance->process($this->container));
    }
}