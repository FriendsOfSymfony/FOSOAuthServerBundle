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

namespace FOS\OAuthServerBundle\Tests\DependencyInjection;

use FOS\OAuthServerBundle\DependencyInjection\FOSOAuthServerExtension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\Routing\Loader\XmlFileLoader;

class FOSOAuthServerExtensionTest extends \PHPUnit\Framework\TestCase
{
    private $container;

    public function setUp()
    {
        $parameterBag = new ParameterBag();
        $this->container = new ContainerBuilder($parameterBag);

        parent::setUp();
    }

    public function testLoadAuthorizeRouting()
    {
        $locator = new FileLocator();
        $loader = new XmlFileLoader($locator);

        $collection = $loader->load(__DIR__.'/../../Resources/config/routing/authorize.xml');
        $authorizeRoute = $collection->get('fos_oauth_server_authorize');
        $this->assertSame('/oauth/v2/auth', $authorizeRoute->getPath());
        $this->assertSame(['GET', 'POST'], $authorizeRoute->getMethods());
    }

    public function testLoadTokenRouting()
    {
        $locator = new FileLocator();
        $loader = new XmlFileLoader($locator);

        $collection = $loader->load(__DIR__.'/../../Resources/config/routing/token.xml');
        $tokenRoute = $collection->get('fos_oauth_server_token');
        $this->assertSame('/oauth/v2/token', $tokenRoute->getPath());
        $this->assertSame(['GET', 'POST'], $tokenRoute->getMethods());
    }

    public function testWithoutService()
    {
        $config = [
            'db_driver' => 'orm',
            'client_class' => 'dumb_class',
            'access_token_class' => 'dumb_access_token_class',
            'refresh_token_class' => 'dumb_refresh_token_class',
            'auth_code_class' => 'dumb_auth_code_class',
        ];
        $instance = new FOSOAuthServerExtension();
        $instance->load([$config], $this->container);

        $this->assertSame(
            $this->container->getParameter('fos_oauth_server.server.options'),
            []
        );
    }

    public function testMultilineScopes()
    {
        $scopes = <<<'SCOPES'
scope1
scope2
scope3 scope4
SCOPES;

        $config = [
            'db_driver' => 'orm',
            'client_class' => 'dumb_class',
            'access_token_class' => 'dumb_access_token_class',
            'refresh_token_class' => 'dumb_refresh_token_class',
            'auth_code_class' => 'dumb_auth_code_class',
            'service' => [
                'options' => [
                    'supported_scopes' => $scopes,
                ],
            ],
        ];
        $instance = new FOSOAuthServerExtension();
        $instance->load([$config], $this->container);

        $this->assertSame(
            $this->container->getParameter('fos_oauth_server.server.options'),
            ['supported_scopes' => 'scope1 scope2 scope3 scope4']
        );
    }
}
