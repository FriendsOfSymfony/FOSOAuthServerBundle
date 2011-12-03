<?php

namespace Alb\OAuth2ServerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

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
        $rootNode = $treeBuilder->root('alb_o_auth2_server');

        $rootNode
            ->children()
                ->scalarNode('db_driver')->cannotBeOverwritten()->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('storage_service')->defaultValue('alb.oauth2.server.server_service.storage.default')->cannotBeEmpty()->end()
                ->scalarNode('user_provider_service')->end()
                ->scalarNode('oauth2_client_class')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('oauth2_access_token_class')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('oauth2_auth_code_class')->isRequired()->cannotBeEmpty()->end()
            ->end();

        return $treeBuilder;
    }
}

