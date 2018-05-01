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
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
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

    public function testShouldImplementConfigurationInterface()
    {
        $rc = new \ReflectionClass(FOSOAuthServerExtension::class);

        $this->assertTrue($rc->isSubclassOf(Extension::class));
    }

    public function testCouldBeConstructedWithoutAnyArguments()
    {
        try {
            new FOSOAuthServerExtension();

            // no exceptions were thrown
            self::assertTrue(true);
        } catch (\Exception $exception) {
            $this->fail($exception->getMessage());
        }
    }

    public function testShouldLoadAuthorizeRelatedServicesIfAuthorizationIsEnabled()
    {
        $container = new ContainerBuilder();

        $extension = new FOSOAuthServerExtension();
        $extension->load([[
            'db_driver' => 'orm',
            'client_class' => 'aClientClass',
            'access_token_class' => 'anAccessTokenClass',
            'refresh_token_class' => 'aRefreshTokenClass',
            'auth_code_class' => 'anAuthCodeClass',
            'authorize' => true,
        ]], $container);

        $this->assertTrue($container->hasDefinition('fos_oauth_server.authorize.form'));
        $this->assertTrue($container->hasDefinition('fos_oauth_server.authorize.form.type'));
        $this->assertTrue($container->hasDefinition('fos_oauth_server.authorize.form.handler.default'));
        $this->assertTrue($container->hasDefinition('fos_oauth_server.controller.authorize'));
    }

    public function testShouldNotLoadAuthorizeRelatedServicesIfAuthorizationIsDisabled()
    {
        $container = new ContainerBuilder();

        $extension = new FOSOAuthServerExtension();
        $extension->load([[
            'db_driver' => 'orm',
            'client_class' => 'aClientClass',
            'access_token_class' => 'anAccessTokenClass',
            'refresh_token_class' => 'aRefreshTokenClass',
            'auth_code_class' => 'anAuthCodeClass',
            'authorize' => false,
        ]], $container);

        $this->assertFalse($container->hasDefinition('fos_oauth_server.authorize.form'));
        $this->assertFalse($container->hasDefinition('fos_oauth_server.authorize.form.type'));
        $this->assertFalse($container->hasDefinition('fos_oauth_server.authorize.form.handler.default'));
        $this->assertFalse($container->hasDefinition('fos_oauth_server.controller.authorize'));
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

    public function testStringSupportedScopes()
    {
        $scopes = 'scope1 scope2 scope3 scope4';

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
            [
                'supported_scopes' => 'scope1 scope2 scope3 scope4',
            ]
        );
    }

    public function testArraySupportedScopes()
    {
        $scopes = ['scope1', 'scope2', 'scope3', 'scope4'];

        $config = [
            'db_driver' => 'orm',
            'client_class' => 'dumb_class',
            'access_token_class' => 'dumb_access_token_class',
            'refresh_token_class' => 'dumb_refresh_token_class',
            'auth_code_class' => 'dumb_auth_code_class',
            'service' => [
                'options' => [
                    'supported_scopes' => $scopes,
                    'enforce_redirect' => true,
                ],
            ],
        ];
        $instance = new FOSOAuthServerExtension();
        $instance->load([$config], $this->container);

        $this->assertSame(
            $this->container->getParameter('fos_oauth_server.server.options'),
            [
                'supported_scopes' => 'scope1 scope2 scope3 scope4',
                'enforce_redirect' => true,
            ]
        );
    }

    public function testArraySupportedScopesWithSpace()
    {
        $scopes = ['scope1 scope2', 'scope3', 'scope4'];

        $config = [
            'db_driver' => 'orm',
            'client_class' => 'dumb_class',
            'access_token_class' => 'dumb_access_token_class',
            'refresh_token_class' => 'dumb_refresh_token_class',
            'auth_code_class' => 'dumb_auth_code_class',
            'service' => [
                'options' => [
                    'supported_scopes' => $scopes,
                    'enforce_redirect' => true,
                ],
            ],
        ];
        $instance = new FOSOAuthServerExtension();

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('The array notation for supported_scopes should not contain spaces in array items. Either use full array notation or use the string notation for supported_scopes. See https://git.io/vx1X0 for more informations.');
        $instance->load([$config], $this->container);
    }

    public function testShouldAliasServivesWhenCustomDriverIsUsed()
    {
        $container = new ContainerBuilder();
        $extension = new FOSOAuthServerExtension();
        $extension->load([[
            'db_driver' => 'custom',
            'client_class' => 'aClientClass',
            'access_token_class' => 'anAccessTokenClass',
            'refresh_token_class' => 'aRefreshTokenClass',
            'auth_code_class' => 'anAuthCodeClass',
            'service' => [
                'storage' => 'fos_oauth_server.storage.default',
                'user_provider' => null,
                'client_manager' => 'the_client_manager_id',
                'access_token_manager' => 'the_access_token_manager_id',
                'refresh_token_manager' => 'the_refresh_token_manager_id',
                'auth_code_manager' => 'the_auth_code_manager_id',
            ],
        ]], $container);

        $this->assertTrue($container->hasAlias('fos_oauth_server.storage'));
        $this->assertSame('fos_oauth_server.storage.default', (string) $container->getAlias('fos_oauth_server.storage'));

        $this->assertTrue($container->hasAlias('fos_oauth_server.client_manager'));
        $this->assertSame('the_client_manager_id', (string) $container->getAlias('fos_oauth_server.client_manager'));

        $this->assertTrue($container->hasAlias('fos_oauth_server.access_token_manager'));
        $this->assertSame('the_access_token_manager_id', (string) $container->getAlias('fos_oauth_server.access_token_manager'));

        $this->assertTrue($container->hasAlias('fos_oauth_server.refresh_token_manager'));
        $this->assertSame('the_refresh_token_manager_id', (string) $container->getAlias('fos_oauth_server.refresh_token_manager'));

        $this->assertTrue($container->hasAlias('fos_oauth_server.auth_code_manager'));
        $this->assertSame('the_auth_code_manager_id', (string) $container->getAlias('fos_oauth_server.auth_code_manager'));
    }
}
