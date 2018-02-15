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

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
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
