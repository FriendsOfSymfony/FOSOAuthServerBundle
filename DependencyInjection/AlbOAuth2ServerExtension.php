<?php

namespace Alb\OAuth2ServerBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class AlbOAuth2ServerExtension extends Extension
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
                ->setAlias('alb.oauth2.server.server_service.storage', $config['storage_service']);
        }

        if (isset($config['user_provider_service'])) {
            $container
                ->getDefinition('alb.oauth2.server.server_service.storage.default')
                ->replaceArgument(3, new Reference($config['user_provider_service']))
                ;
        }

        $container->setParameter('alb.oauth2.server.model.client.class', $config['oauth2_client_class']);
        $container->setParameter('alb.oauth2.server.model.access.token.class', $config['oauth2_access_token_class']);
        $container->setParameter('alb.oauth2.server.model.auth.code.class', $config['oauth2_auth_code_class']);

        $container->setParameter('alb.oauth2.server.server_service.options', $config['oauth2_options']);
    }
}
