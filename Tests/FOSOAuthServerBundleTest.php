<?php

namespace FOS\OAuthServerBundle\Tests;

use FOS\OAuthServerBundle\DependencyInjection\Compiler;
use FOS\OAuthServerBundle\DependencyInjection\FOSOAuthServerExtension;
use FOS\OAuthServerBundle\DependencyInjection\Security\Factory\OAuthFactory;
use FOS\OAuthServerBundle\FOSOAuthServerBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;

class FOSOAuthServerBundleTest extends TestCase
{
    public function testConstruction()
    {
        $bundle = new FOSOAuthServerBundle();

        $objectReflection = new \ReflectionObject($bundle);

        $propertyReflection = $objectReflection->getProperty('extension');
        $propertyReflection->setAccessible(true);

        $this->assertInstanceOf(FOSOAuthServerExtension::class, $propertyReflection->getValue($bundle));

        $propertyReflection = $objectReflection->getProperty('kernelVersion');
        $propertyReflection->setAccessible(true);

        $this->assertEquals(Kernel::VERSION, $propertyReflection->getValue($bundle));
    }

    public function testBuildForSymfonyHigherThan20()
    {
        $bundle = new FOSOAuthServerBundle();
        $objectReflection = new \ReflectionObject($bundle);

        $propertyReflection = $objectReflection->getProperty('kernelVersion');
        $propertyReflection->setAccessible(true);
        $propertyReflection->setValue($bundle, '2.1.0');

        /** @var ContainerBuilder|\\PHPUnit\Framework\MockObject\MockObject $containerBuilder */
        $containerBuilder = $this->getMockBuilder(ContainerBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getExtension',
                    'addCompilerPass',
                ]
            )
            ->getMock();

        /** @var SecurityExtension|\\PHPUnit\Framework\MockObject\MockObject $securityExtension */
        $securityExtension = $this->getMockBuilder(SecurityExtension::class)
            ->disableOriginalConstructor()
            ->getMock();

        $containerBuilder
            ->expects($this->at(0))
            ->method('getExtension')
            ->with('security')
            ->willReturn($securityExtension);

        $securityExtension
            ->expects($this->at(0))
            ->method('addSecurityListenerFactory')
            ->with(new OAuthFactory())
            ->willReturn(null);

        $containerBuilder
            ->expects($this->at(1))
            ->method('addCompilerPass')
            ->withConsecutive(
                new Compiler\GrantExtensionsCompilerPass(),
                new Compiler\TokenStorageCompilerPass(),
                new Compiler\RequestStackCompilerPass()
            )
            ->willReturnOnConsecutiveCalls(
                $containerBuilder,
                $containerBuilder,
                $containerBuilder
            );

        $bundle->build($containerBuilder);
    }

    public function testBuildForSymfony20()
    {
        $bundle = new FOSOAuthServerBundle();
        $objectReflection = new \ReflectionObject($bundle);

        $propertyReflection = $objectReflection->getProperty('kernelVersion');
        $propertyReflection->setAccessible(true);
        $propertyReflection->setValue($bundle, '2.0.0');

        /** @var ContainerBuilder|\\PHPUnit\Framework\MockObject\MockObject $containerBuilder */
        $containerBuilder = $this->getMockBuilder(ContainerBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'addCompilerPass',
                ]
            )
            ->getMock();

        $containerBuilder
            ->expects($this->at(1))
            ->method('addCompilerPass')
            ->withConsecutive(
                new Compiler\GrantExtensionsCompilerPass(),
                new Compiler\TokenStorageCompilerPass(),
                new Compiler\RequestStackCompilerPass()
            )
            ->willReturnOnConsecutiveCalls(
                $containerBuilder,
                $containerBuilder,
                $containerBuilder
            );

        $bundle->build($containerBuilder);
    }
}
