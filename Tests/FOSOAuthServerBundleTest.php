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

namespace FOS\OAuthServerBundle\Tests;

use FOS\OAuthServerBundle\DependencyInjection\Compiler;
use FOS\OAuthServerBundle\DependencyInjection\Security\Factory\OAuthFactory;
use FOS\OAuthServerBundle\FOSOAuthServerBundle;
use Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class FOSOAuthServerBundleTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp()
    {
        parent::setUp();
    }

    public function testConstruction()
    {
        $bundle = new FOSOAuthServerBundle();

        /** @var ContainerBuilder|\PHPUnit_Framework_MockObject_MockObject $containerBuilder */
        $containerBuilder = $this->getMockBuilder(ContainerBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getExtension',
                'addCompilerPass',
            ])
            ->getMock()
        ;

        /** @var SecurityExtension|\PHPUnit_Framework_MockObject_MockObject $securityExtension */
        $securityExtension = $this->getMockBuilder(SecurityExtension::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $containerBuilder
            ->expects($this->at(0))
            ->method('getExtension')
            ->with('security')
            ->willReturn($securityExtension)
        ;

        $securityExtension
            ->expects($this->at(0))
            ->method('addSecurityListenerFactory')
            ->with(new OAuthFactory())
            ->willReturn(null)
        ;

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
            )
        ;

        $this->assertNull($bundle->build($containerBuilder));
    }
}
