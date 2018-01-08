<?php

/*
 * This file is part of the FOSOAuthServerBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\OAuthServerBundle\DependencyInjection\Security\Factory;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;

/**
 * OAuthFactory class.
 *
 * @author Arnaud Le Blanc <arnaud.lb@gmail.com>
 */
class OAuthFactory implements SecurityFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function create(ContainerBuilder $container, $id, $config, $userProvider, $defaultEntryPoint)
    {
        $providerId = 'security.authentication.provider.fos_oauth_server.'.$id;
        if (class_exists(ChildDefinition::class)) {
            $definition = new ChildDefinition('fos_oauth_server.security.authentication.provider');
        } else {
            $definition = new DefinitionDecorator('fos_oauth_server.security.authentication.provider');
        }
        $container
            ->setDefinition($providerId, $definition)
            ->replaceArgument(0, new Reference($userProvider))
        ;

        $listenerId = 'security.authentication.listener.fos_oauth_server.'.$id;

        if (class_exists(ChildDefinition::class)) {
            $definition = new ChildDefinition('fos_oauth_server.security.authentication.listener');
        } else {
            $definition = new DefinitionDecorator('fos_oauth_server.security.authentication.listener');
        }
        $container->setDefinition($listenerId, $definition);

        return array($providerId, $listenerId, 'fos_oauth_server.security.entry_point');
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
