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
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class FOSOAuthServerBundleTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp() : void
    {
        parent::setUp();
    }

    public function testConstruction()
    {
        $bundle = new FOSOAuthServerBundle();

        /** @var ContainerBuilder|MockObject $containerBuilder */
        $containerBuilder = $this->getMockBuilder(ContainerBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getExtension',
                'addCompilerPass',
            ])
            ->getMock()
        ;

        /** @var SecurityExtension|MockObject $securityExtension */
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
                [new Compiler\GrantExtensionsCompilerPass()],
                [new Compiler\RequestStackCompilerPass()]
            )
            ->willReturnOnConsecutiveCalls(
                $containerBuilder,
                $containerBuilder,
                $containerBuilder
            )
        ;

        self::assertNull($bundle->build($containerBuilder));
    }
}
