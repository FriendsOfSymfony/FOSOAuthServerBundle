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

namespace FOS\OAuthServerBundle\Tests\DependencyInjection\Security\Factory;

use Exception;
use FOS\OAuthServerBundle\DependencyInjection\Security\Factory\OAuthFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class OAuthFactoryTest.
 *
 * @author Nikola Petkanski <nikola@petkanski.com>
 */
class OAuthFactoryTest extends TestCase
{
    /**
     * @var OAuthFactory
     */
    protected $instance;

    /**
     * @var string
     */
    protected $definitionDecoratorClass;

    /**
     * @var string
     */
    protected $childDefinitionClass;

    public function setUp(): void
    {
        $this->definitionDecoratorClass = DefinitionDecorator::class;
        $this->childDefinitionClass = ChildDefinition::class;

        $this->instance = new OAuthFactory();

        parent::setUp();
    }

    public function testGetPosition(): void
    {
        self::assertSame('pre_auth', $this->instance->getPosition());
    }

    public function testGetKey(): void
    {
        self::assertSame('fos_oauth', $this->instance->getKey());
    }

    public function testAddConfigurationDoesNothing(): void
    {
        $nodeDefinition = $this->getMockBuilder(NodeDefinition::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        self::assertNull($this->instance->addConfiguration($nodeDefinition));
    }

    public function testCreate(): void
    {
        if (class_exists($this->childDefinitionClass)) {
            $this->useChildDefinition();
        } elseif (class_exists($this->definitionDecoratorClass)) {
            $this->useDefinitionDecorator();
        } else {
            throw new Exception('Neither DefinitionDecorator nor ChildDefinition exist');
        }
    }

    protected function useDefinitionDecorator(): void
    {
        $container = $this->getMockBuilder(ContainerBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'setDefinition',
            ])
            ->getMock()
        ;
        $id = '12';
        $config = [];
        $userProvider = 'mock.user.provider.service';
        $defaultEntryPoint = '';

        $definition = $this->getMockBuilder(Definition::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $container
            ->expects($this->exactly(2))
            ->method('setDefinition')
            ->withConsecutive(
                [
                    'security.authentication.provider.fos_oauth_server.'.$id,
                    new $this->definitionDecoratorClass('fos_oauth_server.security.authentication.provider'),
                ],
                [
                    'security.authentication.listener.fos_oauth_server.'.$id,
                    new $this->definitionDecoratorClass('fos_oauth_server.security.authentication.listener'),
                ]
            )
            ->willReturnOnConsecutiveCalls(
                $definition,
                null
            )
        ;

        $definition
            ->expects($this->once())
            ->method('replaceArgument')
            ->with(0, new Reference($userProvider))
            ->willReturn(null)
        ;

        self::assertSame([
            'security.authentication.provider.fos_oauth_server.'.$id,
            'security.authentication.listener.fos_oauth_server.'.$id,
            'fos_oauth_server.security.entry_point',
        ], $this->instance->create($container, $id, $config, $userProvider, $defaultEntryPoint));
    }

    protected function useChildDefinition(): void
    {
        $container = $this->getMockBuilder(ContainerBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'setDefinition',
            ])
            ->getMock()
        ;
        $id = '12';
        $config = [];
        $userProvider = 'mock.user.provider.service';
        $defaultEntryPoint = '';

        $definition = $this->getMockBuilder(Definition::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $container
            ->expects($this->exactly(2))
            ->method('setDefinition')
            ->withConsecutive(
                [
                    'security.authentication.provider.fos_oauth_server.'.$id,
                    new $this->childDefinitionClass('fos_oauth_server.security.authentication.provider'),
                ],
                [
                    'security.authentication.listener.fos_oauth_server.'.$id,
                    new $this->childDefinitionClass('fos_oauth_server.security.authentication.listener'),
                ]
            )
            ->willReturnOnConsecutiveCalls(
                $definition,
                null
            )
        ;

        $definition
            ->expects($this->once())
            ->method('replaceArgument')
            ->with(0, new Reference($userProvider))
            ->willReturn(null)
        ;

        self::assertSame([
            'security.authentication.provider.fos_oauth_server.'.$id,
            'security.authentication.listener.fos_oauth_server.'.$id,
            'fos_oauth_server.security.entry_point',
        ], $this->instance->create($container, $id, $config, $userProvider, $defaultEntryPoint));
    }
}
