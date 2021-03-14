<?php

namespace FOS\OAuthServerBundle\Tests\DependencyInjection\Security\Factory;

use FOS\OAuthServerBundle\DependencyInjection\Security\Factory\OAuthFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class OAuthFactoryTest
 * @package FOS\OAuthServerBundle\Tests\DependencyInjection\Security\Factory
 * @author Nikola Petkanski <nikola@petkanski.com>
 */
class OAuthFactoryTest extends TestCase
{
    /**
     * @var OAuthFactory
     */
    protected $instance;

    public function setUp(): void
    {
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

    public function testCreate(): void
    {
        $container = $this->getMockBuilder(ContainerBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'setDefinition',

                ]
            )
            ->getMock();
        $id = '12';
        $config = [];
        $userProvider = 'mock.user.provider.service';
        $defaultEntryPoint = '';

        $definition = $this->getMockBuilder(Definition::class)
            ->disableOriginalConstructor()
            ->getMock();

        $container
            ->expects(self::exactly(2))
            ->method('setDefinition')
            ->withConsecutive(
                [
                    'security.authentication.provider.fos_oauth_server.'.$id,
                    new ChildDefinition('fos_oauth_server.security.authentication.provider'),
                ],
                [
                    'security.authentication.listener.fos_oauth_server.'.$id,
                    new ChildDefinition('fos_oauth_server.security.authentication.listener'),
                ]
            )
            ->willReturnOnConsecutiveCalls(
                $definition,
                null
            );

        $definition
            ->expects(self::once())
            ->method('replaceArgument')
            ->with(0, new Reference($userProvider))
            ->willReturn(null);

        self::assertSame(
            [
                'security.authentication.provider.fos_oauth_server.'.$id,
                'security.authentication.listener.fos_oauth_server.'.$id,
                'fos_oauth_server.security.entry_point',
            ],
            $this->instance->create($container, $id, $config, $userProvider, $defaultEntryPoint)
        );
    }

    public function testAddConfigurationDoesNothing(): void
    {
        $nodeDefinition = $this->getMockBuilder(NodeDefinition::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->instance->addConfiguration($nodeDefinition);
    }
}
