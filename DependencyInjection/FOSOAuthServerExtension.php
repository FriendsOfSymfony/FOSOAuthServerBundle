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

namespace FOS\OAuthServerBundle\DependencyInjection;

use FOS\OAuthServerBundle\Util\LegacyFormHelper;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class FOSOAuthServerExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $processor = new Processor();
        $configuration = new Configuration();

        $config = $processor->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        if ('custom' !== $config['db_driver']) {
            $loader->load(sprintf('%s.xml', $config['db_driver']));
        }

        foreach (['oauth', 'security'] as $basename) {
            $loader->load(sprintf('%s.xml', $basename));
        }

        $container->setAlias('fos_oauth_server.storage', $config['service']['storage']);
        $container->setAlias('fos_oauth_server.client_manager', $config['service']['client_manager']);
        $container->setAlias('fos_oauth_server.access_token_manager', $config['service']['access_token_manager']);
        $container->setAlias('fos_oauth_server.refresh_token_manager', $config['service']['refresh_token_manager']);
        $container->setAlias('fos_oauth_server.auth_code_manager', $config['service']['auth_code_manager']);

        if (null !== $config['service']['user_provider']) {
            $container->setAlias('fos_oauth_server.user_provider', new Alias($config['service']['user_provider'], false));
        }

        $options = $config['service']['options'];
        if (is_array($options['supported_scopes'] ?? null)) {
            $options['supported_scopes'] = $this->computeArraySupportedScopes($options['supported_scopes']);
        }
        $container->setParameter('fos_oauth_server.server.options', $options);

        $this->remapParametersNamespaces($config, $container, [
            '' => [
                'model_manager_name' => 'fos_oauth_server.model_manager_name',
                'client_class' => 'fos_oauth_server.model.client.class',
                'access_token_class' => 'fos_oauth_server.model.access_token.class',
                'refresh_token_class' => 'fos_oauth_server.model.refresh_token.class',
                'auth_code_class' => 'fos_oauth_server.model.auth_code.class',
            ],
            'template' => 'fos_oauth_server.template.%s',
        ]);

        // Handle the MongoDB document manager name in a specific way as it does not have a registry to make it easy
        // TODO: change it when bumping the requirement to Symfony 2.1
        if ('mongodb' === $config['db_driver']) {
            if (null === $config['model_manager_name']) {
                $container->setAlias('fos_oauth_server.document_manager', new Alias('doctrine.odm.mongodb.document_manager', false));
            } else {
                $container->setAlias('fos_oauth_server.document_manager', new Alias(
                    sprintf('doctrine.odm.%s_mongodb.document_manager',
                    $config['model_manager_name']),
                    false
                ));
            }
        }

        // Entity manager factory definition
        // TODO: Go back to xml configuration when bumping the requirement to Symfony >=2.6
        if ('orm' === $config['db_driver']) {
            $ormEntityManagerDefinition = $container->getDefinition('fos_oauth_server.entity_manager');
            $ormEntityManagerDefinition->setFactory([new Reference('doctrine'), 'getManager']);
        }

        if (!empty($config['authorize'])) {
            $this->loadAuthorize($config['authorize'], $container, $loader);

            // Authorize form factory definition
            // TODO: Go back to xml configuration when bumping the requirement to Symfony >=2.6
            $authorizeFormDefinition = $container->getDefinition('fos_oauth_server.authorize.form');
            $authorizeFormDefinition->setFactory([new Reference('form.factory'), 'createNamed']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return 'fos_oauth_server';
    }

    protected function remapParameters(array $config, ContainerBuilder $container, array $map)
    {
        foreach ($map as $name => $paramName) {
            if (array_key_exists($name, $config)) {
                $container->setParameter($paramName, $config[$name]);
            }
        }
    }

    protected function remapParametersNamespaces(array $config, ContainerBuilder $container, array $namespaces)
    {
        foreach ($namespaces as $ns => $map) {
            if ($ns) {
                if (!array_key_exists($ns, $config)) {
                    continue;
                }
                $namespaceConfig = $config[$ns];
            } else {
                $namespaceConfig = $config;
            }

            if (is_array($map)) {
                $this->remapParameters($namespaceConfig, $container, $map);
            } else {
                foreach ($namespaceConfig as $name => $value) {
                    $container->setParameter(sprintf($map, $name), $value);
                }
            }
        }
    }

    protected function loadAuthorize(array $config, ContainerBuilder $container, XmlFileLoader $loader)
    {
        $loader->load('authorize.xml');

        $container->setAlias('fos_oauth_server.authorize.form.handler', $config['form']['handler']);
        unset($config['form']['handler']);

        if (!LegacyFormHelper::isLegacy() && $config['form']['type'] === 'fos_oauth_server_authorize') {
            $authorizeFormTypeDefinition = $container->getDefinition('fos_oauth_server.authorize.form.type');
            $config['form']['type'] = $authorizeFormTypeDefinition->getClass();
        }

        $this->remapParametersNamespaces($config, $container, [
            'form' => 'fos_oauth_server.authorize.form.%s',
        ]);
    }

    private function computeArraySupportedScopes(array $supportedScopes)
    {
        foreach ($supportedScopes as $scope) {
            if (false !== mb_strpos($scope, ' ')) {
                throw new InvalidConfigurationException('The array notation for supported_scopes should not contain spaces in array items. Either use full array notation or use the string notation for supported_scopes. See https://git.io/vx1X0 for more informations.');
            }
        }

        return implode(' ', $supportedScopes);
    }
}
