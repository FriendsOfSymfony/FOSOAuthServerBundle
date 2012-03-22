<?php

/*
 * This file is part of the FOSOAuthServerBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\OAuthServerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('fos_oauth_server');

        $rootNode
            ->children()
                ->scalarNode('db_driver')->cannotBeOverwritten()->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('storage_service')->defaultValue('fos_oauth_server.server_service.storage.default')->cannotBeEmpty()->end()
                ->scalarNode('user_provider_service')->end()
                ->scalarNode('oauth_client_class')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('oauth_access_token_class')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('oauth_refresh_token_class')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('oauth_auth_code_class')->isRequired()->cannotBeEmpty()->end()
                ->arrayNode('oauth_options')
                    ->useAttributeAsKey('key')
                    ->treatNullLike(array())
                    ->prototype('scalar')->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}

