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

        $supportedDrivers = array('orm', 'mongodb', 'propel');

        $rootNode
            ->children()
                ->scalarNode('db_driver')
                    ->validate()
                        ->ifNotInArray($supportedDrivers)
                        ->thenInvalid('The driver %s is not supported. Please choose one of ' . json_encode($supportedDrivers))
                    ->end()
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('client_class')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('access_token_class')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('refresh_token_class')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('auth_code_class')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('model_manager_name')->defaultNull()->end()
            ->end();

        $this->addAuthorizeSection($rootNode);
        $this->addServiceSection($rootNode);
        $this->addTemplateSection($rootNode);

        return $treeBuilder;
    }

    private function addAuthorizeSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode('authorize')
                    ->addDefaultsIfNotSet()
                    ->canBeUnset()
                    ->children()
                        ->arrayNode('form')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('type')->defaultValue('fos_oauth_server_authorize')->end()
                                ->scalarNode('handler')->defaultValue('fos_oauth_server.authorize.form.handler.default')->end()
                                ->scalarNode('name')->defaultValue('fos_oauth_server_authorize_form')->cannotBeEmpty()->end()
                                ->arrayNode('validation_groups')
                                    ->prototype('scalar')->end()
                                    ->defaultValue(array('Authorize', 'Default'))
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    private function addServiceSection(ArrayNodeDefinition $node)
    {
        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('service')
                    ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('storage')->defaultValue('fos_oauth_server.storage.default')->cannotBeEmpty()->end()
                            ->scalarNode('user_provider')->defaultNull()->end()
                            ->scalarNode('client_manager')->defaultValue('fos_oauth_server.client_manager.default')->end()
                            ->scalarNode('access_token_manager')->defaultValue('fos_oauth_server.access_token_manager.default')->end()
                            ->scalarNode('refresh_token_manager')->defaultValue('fos_oauth_server.refresh_token_manager.default')->end()
                            ->scalarNode('auth_code_manager')->defaultValue('fos_oauth_server.auth_code_manager.default')->end()
                            ->arrayNode('options')
                                ->useAttributeAsKey('key')
                                ->treatNullLike(array())
                                ->prototype('scalar')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    private function addTemplateSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode('template')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('engine')->defaultValue('twig')->end()
                    ->end()
                ->end()
            ->end();
    }
}
