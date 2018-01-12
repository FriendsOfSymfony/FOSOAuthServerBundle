<?php

namespace FOS\OAuthServerBundle\Tests\DependencyInjection\Compiler;

use FOS\OAuthServerBundle\DependencyInjection\Compiler\GrantExtensionsCompilerPass;
use FOS\OAuthServerBundle\Storage\GrantExtensionDispatcherInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class GrantExtensionsCompilerPassTest
 * @package FOS\OAuthServerBundle\Tests\DependencyInjection\Compiler
 * @author Nikola Petkanski <nikola@petkanski.com>
 */
class GrantExtensionsCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var GrantExtensionsCompilerPass
     */
    protected $instance;

    public function setUp()
    {
        $this->instance = new GrantExtensionsCompilerPass();

        parent::setUp();
    }

    public function testProcessWillNotDoAnythingIfTheStorageDoesNotImplementOurInterface()
    {
        $container = $this->getMockBuilder(ContainerBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'findDefinition',
                'getParameterBag',

            ])
            ->getMock()
        ;
        $storageDefinition = $this->createMock(Definition::class);
        $parameterBag = $this->createMock(ParameterBag::class);

        $className = 'stdClassUnresolved' . random_bytes(5);
        $resolvedClassName = 'stdClass';

        $container
            ->expects($this->once())
            ->method('findDefinition')
            ->with('fos_oauth_server.storage')
            ->willReturn($storageDefinition)
        ;

        $storageDefinition
            ->expects($this->once())
            ->method('getClass')
            ->with()
            ->willReturn($className)
        ;

        $container
            ->expects($this->once())
            ->method('getParameterBag')
            ->with()
            ->willReturn($parameterBag)
        ;

        $parameterBag
            ->expects($this->once())
            ->method('resolveValue')
            ->with($className)
            ->willReturn($resolvedClassName)
        ;

        $this->assertNull($this->instance->process($container));
    }

    public function testProcessWillFailIfUriIsEmpty()
    {
        $container = $this->getMockBuilder(ContainerBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'findDefinition',
                'getParameterBag',
                'findTaggedServiceIds',
            ])
            ->getMock()
        ;
        $storageDefinition = $this->createMock(Definition::class);
        $parameterBag = $this->createMock(ParameterBag::class);

        $storageInstance = $this->createMock(GrantExtensionDispatcherInterface::class);

        $className = 'stdClassUnresolved' . random_bytes(5);
        $resolvedClassName = get_class($storageInstance);

        $container
            ->expects($this->once())
            ->method('findDefinition')
            ->with('fos_oauth_server.storage')
            ->willReturn($storageDefinition)
        ;

        $storageDefinition
            ->expects($this->once())
            ->method('getClass')
            ->with()
            ->willReturn($className)
        ;

        $container
            ->expects($this->once())
            ->method('getParameterBag')
            ->with()
            ->willReturn($parameterBag)
        ;

        $parameterBag
            ->expects($this->once())
            ->method('resolveValue')
            ->with($className)
            ->willReturn($resolvedClassName)
        ;

        $data = [
            'service.id.1' => [
                [
                    'uri' => 'uri11',
                ],
                [
                    'uri' => '',
                ],
            ],
        ];

        $container
            ->expects($this->once())
            ->method('findTaggedServiceIds')
            ->with('fos_oauth_server.grant_extension')
            ->willReturn($data)
        ;

        $idx = 0;
        foreach ($data as $id => $tags) {
            foreach ($tags as $tag) {
                if (empty($tag['uri'])) {
                    $exceptionMessage = sprintf('Service "%s" must define the "uri" attribute on "fos_oauth_server.grant_extension" tags.', $id);
                    $this->expectExceptionMessage($exceptionMessage);
                    break;
                }

                $storageDefinition
                    ->expects($this->at(++$idx))
                    ->method('addMethodCall')
                    ->with(
                        'setGrantExtension',
                        [
                            $tag['uri'],
                            new Reference($id)
                        ]
                    )
                ;
            }
        }

        $this->expectException(InvalidArgumentException::class);

        $this->assertNull($this->instance->process($container));
    }

    public function testProcess()
    {
        $container = $this->getMockBuilder(ContainerBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'findDefinition',
                'getParameterBag',
                'findTaggedServiceIds',
            ])
            ->getMock()
        ;
        $storageDefinition = $this->createMock(Definition::class);
        $parameterBag = $this->createMock(ParameterBag::class);

        $storageInstance = $this->createMock(GrantExtensionDispatcherInterface::class);

        $className = 'stdClassUnresolved' . random_bytes(5);
        $resolvedClassName = get_class($storageInstance);

        $container
            ->expects($this->once())
            ->method('findDefinition')
            ->with('fos_oauth_server.storage')
            ->willReturn($storageDefinition)
        ;

        $storageDefinition
            ->expects($this->once())
            ->method('getClass')
            ->with()
            ->willReturn($className)
        ;

        $container
            ->expects($this->once())
            ->method('getParameterBag')
            ->with()
            ->willReturn($parameterBag)
        ;

        $parameterBag
            ->expects($this->once())
            ->method('resolveValue')
            ->with($className)
            ->willReturn($resolvedClassName)
        ;

        $data = [
            'service.id.1' => [
                [
                    'uri' => 'uri11',
                ],
                [
                    'uri' => 'uri12',
                ],
            ],
            'service.id.2' => [
                [
                    'uri' => 'uri21',
                ],
                [
                    'uri' => 'uri22',
                ],
            ],
        ];

        $container
            ->expects($this->once())
            ->method('findTaggedServiceIds')
            ->with('fos_oauth_server.grant_extension')
            ->willReturn($data)
        ;

        $idx = 0;
        foreach ($data as $id => $tags) {
            foreach ($tags as $tag) {
                $storageDefinition
                    ->expects($this->at(++$idx))
                    ->method('addMethodCall')
                    ->with(
                        'setGrantExtension',
                        [
                            $tag['uri'],
                            new Reference($id)
                        ]
                    )
                ;
            }
        }

        $this->assertNull($this->instance->process($container));
    }
}