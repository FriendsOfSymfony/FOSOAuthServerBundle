<?php

/*
 * This file is part of the FOSOAuthServerBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DependencyInjection;

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
}
