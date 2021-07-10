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

namespace FOS\OAuthServerBundle\DependencyInjection\Security\Factory;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\AuthenticatorFactoryInterface;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * OAuthFactory class.
 *
 * @author Arnaud Le Blanc <arnaud.lb@gmail.com>
 */
class OAuthFactory implements SecurityFactoryInterface, AuthenticatorFactoryInterface
{
    public function createAuthenticator(ContainerBuilder $container, string $firewallName, array $config, string $userProviderId)
    {
        $authenticatorId = 'security.authenticator.oauth2.'.$firewallName;
        $firewallEventDispatcherId = 'security.event_dispatcher.'.$firewallName;

        // authenticator manager
        $firewallAuthenticationProviders = [];
        $authenticators = array_map(function ($firewallName) {
            return new Reference($firewallName);
        }, $firewallAuthenticationProviders);
        $container
            ->setDefinition($managerId = 'security.authenticator.oauth2.'.$firewallName, new ChildDefinition('fos_oauth_server.security.authenticator.manager'))
//            ->replaceArgument(0, $authenticators)
//            ->replaceArgument(2, new Reference($firewallEventDispatcherId))
            ->addTag('monolog.logger', ['channel' => 'security'])
        ;

        $managerLocator = $container->getDefinition('security.authenticator.managers_locator');
        $managerLocator->replaceArgument(0, array_merge($managerLocator->getArgument(0), [$firewallName => new ServiceClosureArgument(new Reference($managerId))]));

        // authenticator manager listener
        $container
            ->setDefinition('security.firewall.authenticator.'.$firewallName, new ChildDefinition('security.firewall.authenticator'))
            ->replaceArgument(0, new Reference($managerId))
        ;

        // user checker listener
        $container
            ->setDefinition('security.listener.user_checker.'.$firewallName, new ChildDefinition('security.listener.user_checker'))
            ->replaceArgument(0, new Reference('security.user_checker.'.$firewallName))
            ->addTag('kernel.event_subscriber', ['dispatcher' => $firewallEventDispatcherId])
        ;

        // Add authenticators to the debug:firewall command
        if ($container->hasDefinition('security.command.debug_firewall')) {
            $debugCommand = $container->getDefinition('security.command.debug_firewall');
            $debugCommand->replaceArgument(3, array_merge($debugCommand->getArgument(3), [$firewallName => $authenticators]));
        }

        return $authenticatorId;
    }

    /**
     * {@inheritdoc}
     */
    public function create(ContainerBuilder $container, $id, $config, $userProvider, $defaultEntryPoint)
    {
        // NOTE: done like this to avoid PHPStan complaining about a missing class for both Symfony v3 and Symfony v4
        $definitionDecorator = 'Symfony\\Component\\DependencyInjection\\DefinitionDecorator';
        $childDefinition = 'Symfony\\Component\\DependencyInjection\\ChildDefinition';
        $definitionClass = $childDefinition;
        if (class_exists($definitionDecorator)) {
            $definitionClass = $definitionDecorator;
        }

        $providerId = 'security.authentication.provider.fos_oauth_server.'.$id;
        $container
            ->setDefinition($providerId, new $definitionClass('fos_oauth_server.security.authentication.provider'))
            ->replaceArgument(0, new Reference($userProvider))
        ;

        $listenerId = 'security.authentication.listener.fos_oauth_server.'.$id;
        $container->setDefinition($listenerId, new $definitionClass('fos_oauth_server.security.authentication.listener'));

        return [$providerId, $listenerId, 'fos_oauth_server.security.entry_point'];
    }

    /**
     * {@inheritdoc}
     */
    public function getPosition()
    {
        return 'pre_auth';
    }

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return 'fos_oauth';
    }

    /**
     * {@inheritdoc}
     */
    public function addConfiguration(NodeDefinition $node)
    {
    }
}
