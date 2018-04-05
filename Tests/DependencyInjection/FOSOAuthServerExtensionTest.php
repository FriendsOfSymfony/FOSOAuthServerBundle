<?php

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
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\Routing\Loader\XmlFileLoader;

class FOSOAuthServerExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldImplementConfigurationInterface()
    {
        $rc = new \ReflectionClass(FOSOAuthServerExtension::class);

        $this->assertTrue($rc->isSubclassOf(Extension::class));
    }

    public function testCouldBeConstructedWithoutAnyArguments()
    {
        new FOSOAuthServerExtension();
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
        $this->assertEquals('/oauth/v2/auth', $authorizeRoute->getPath());
        $this->assertEquals(array('GET', 'POST'), $authorizeRoute->getMethods());
    }

    public function testLoadTokenRouting()
    {
        $locator = new FileLocator();
        $loader = new XmlFileLoader($locator);

        $collection = $loader->load(__DIR__.'/../../Resources/config/routing/token.xml');
        $tokenRoute = $collection->get('fos_oauth_server_token');
        $this->assertEquals('/oauth/v2/token', $tokenRoute->getPath());
        $this->assertEquals(array('GET', 'POST'), $tokenRoute->getMethods());
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
            "service" => [
                "storage" => "fos_oauth_server.storage.default",
                "user_provider" => null,
                "client_manager" => "the_client_manager_id",
                "access_token_manager" => "the_access_token_manager_id",
                "refresh_token_manager" => "the_refresh_token_manager_id",
                "auth_code_manager" => "the_auth_code_manager_id",
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
