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

use FOS\OAuthServerBundle\DependencyInjection\Security\Factory\OAuthFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ServiceLocator;

/**
 * Class OAuthFactoryTest.
 *
 * @author Nikola Petkanski <nikola@petkanski.com>
 */
class OAuthFactoryTest extends TestCase
{
    protected OAuthFactory $instance;
    protected string $definitionDecoratorClass;
    protected string $childDefinitionClass;

    public function setUp(): void
    {
        $this->definitionDecoratorClass = 'Symfony\Component\DependencyInjection\DefinitionDecorator';
        $this->childDefinitionClass = 'Symfony\Component\DependencyInjection\ChildDefinition';

        $this->instance = new OAuthFactory();

        parent::setUp();
    }

    public function testGetPriority(): void
    {
        $this->assertSame(0, $this->instance->getPriority());
    }

    public function testGetKey(): void
    {
        $this->assertSame('fos_oauth', $this->instance->getKey());
    }

    public function testCreateAuthenticator(): void
    {
        $checked = false;
        if (class_exists($this->childDefinitionClass)) {
            $checked = true;
            $this->useChildDefinition();
        }

        if (class_exists($this->definitionDecoratorClass)) {
            $checked = true;
            $this->useDefinitionDecorator();
        }

        if (!$checked) {
            throw new \Exception('Neither DefinitionDecorator nor ChildDefinition exist');
        }
    }

    public function testAddConfigurationDoesNothing(): void
    {
        $nodeDefinition = $this->getMockBuilder(NodeDefinition::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->assertNull($this->instance->addConfiguration($nodeDefinition));
    }

    protected function useDefinitionDecorator()
    {
        $container = $this->getMockBuilder(ContainerBuilder::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $id = '12';
        $config = [];
        $userProvider = 'mock.user.provider.service';

        $definition = $this->getMockBuilder(Definition::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $container
            ->expects($this->exactly(2))
            ->method('setDefinition')
            ->withConsecutive(
                [
                    'security.authenticator.oauth2.'.$id,
                    new $this->definitionDecoratorClass('fos_oauth_server.security.authenticator.manager'),
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
            ->willReturnSelf()
        ;

        $this->assertSame([
            'security.authenticator.oauth2.'.$id,
            'security.authentication.listener.fos_oauth_server.'.$id,
            'fos_oauth_server.security.entry_point',
        ], $this->instance->createAuthenticator($container, $id, $config, $userProvider));
    }

    protected function useChildDefinition(): void
    {
        $container = $this->getMockBuilder(ContainerBuilder::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $id = '12';
        $config = [];
        $userProvider = 'mock.user.provider.service';

        $definition = $this->getMockBuilder(Definition::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $authManagerLocator = $this->getMockBuilder(Definition::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $authManagerLocator
            ->expects($this->once())
            ->method('getArgument')
            ->with(0)
            ->willReturn([])
        ;

        $container
            ->expects($this->once())
            ->method('getDefinition')
            ->with('security.authenticator.managers_locator')
            ->willReturn($authManagerLocator)
        ;

        $container
            ->expects($this->exactly(3))
            ->method('setDefinition')
            ->withConsecutive(
                [
                    'security.authenticator.oauth2.'.$id,
                    new $this->childDefinitionClass('fos_oauth_server.security.authenticator.manager'),
                ],
                [
                    'security.firewall.authenticator.'.$id,
                    new $this->childDefinitionClass('security.firewall.authenticator'),
                ],
                [
                    'security.listener.user_checker.'.$id,
                    new $this->childDefinitionClass('security.listener.user_checker'),
                ]
            )
            ->willReturn(
                $definition
            )
        ;

        $definition
            ->expects($this->exactly(2))
            ->method('replaceArgument')
            ->withConsecutive(
                [0, 'security.authenticator.oauth2.'.$id],
                [0, 'security.user_checker.'.$id],
            )
            ->willReturnSelf()
        ;

        $this->assertSame('security.authenticator.oauth2.'.$id, $this->instance->createAuthenticator($container, $id, $config, $userProvider));
    }
}
