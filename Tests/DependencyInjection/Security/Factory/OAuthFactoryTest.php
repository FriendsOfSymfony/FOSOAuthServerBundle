<?php

namespace FOS\OAuthServerBundle\Tests\DependencyInjection\Security\Factory;

use FOS\OAuthServerBundle\DependencyInjection\Security\Factory\OAuthFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OAuthFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $container = new ContainerBuilder();
        $container->register('auth_provider');

        $factory = new OAuthFactory();

        list($authProviderId,
            $listenerId,
            $entryPointId
            ) = $factory->create($container, 'default', array(), 'user_provider', 'entry_point');

        $this->assertEquals('security.authentication.provider.fos_oauth_server.default', $authProviderId);
        $this->assertEquals('security.authentication.listener.fos_oauth_server.default', $listenerId);
        $this->assertEquals('fos_oauth_server.security.entry_point', $entryPointId);

        $expectedTokenStorageService = interface_exists('Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface')
            ? 'security.token_storage' : 'security.context'
        ;
        $this->assertEquals($expectedTokenStorageService, (string) $container->getDefinition($listenerId)->getArgument(0));

        $this->assertEquals('pre_auth', $factory->getPosition());
        $this->assertEquals('fos_oauth', $factory->getKey());
    }
}
