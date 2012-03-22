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

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class FOSOAuthServerExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $processor = new Processor();
        $configuration = new Configuration($container->get('kernel.debug'));
        $config = $processor->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        if (!in_array(strtolower($config['db_driver']), array('orm', 'odm'))) {
            throw new \InvalidArgumentException(sprintf('Invalid db driver "%s".', $config['db_driver']));
        }
        $loader->load(sprintf('%s.xml', $config['db_driver']));

        if ($config['storage_service']) {
            $container
                ->setAlias('fos_oauth_server.server_service.storage', $config['storage_service']);
        }

        if (isset($config['user_provider_service'])) {
            $container
                ->getDefinition('fos_oauth_server.server_service.storage.default')
                ->replaceArgument(4, new Reference($config['user_provider_service']))
                ;
        }

        $container->setParameter('fos_oauth_server.model.client.class', $config['oauth_client_class']);
        $container->setParameter('fos_oauth_server.model.access.token.class', $config['oauth_access_token_class']);
        $container->setParameter('fos_oauth_server.model.refresh.token.class', $config['oauth_refresh_token_class']);
        $container->setParameter('fos_oauth_server.model.auth.code.class', $config['oauth_auth_code_class']);
        $container->setParameter('fos_oauth_server.server_service.options', $config['oauth_options']);
    }

    public function getAlias()
    {
        return 'fos_oauth_server';
    }
}
