<?php

namespace Alb\OAuth2ServerBundle\DependencyInjection\Security\Factory;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;

/**
 * OAuth2Factory class.
 *
 * @package     AlbOAuth2ServerBundle
 * @subpackage  DependencyInjection
 * @author Arnaud Le Blanc <arnaud.lb@gmail.com>
 */
class OAuth2Factory implements SecurityFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function create(ContainerBuilder $container, $id, $config, $userProvider, $defaultEntryPoint)
    {
        $providerId = 'security.authentication.provider.alb_oauth2_server.'.$id;
        $container
            ->setDefinition($providerId, new DefinitionDecorator('alb.oauth2.server.security.authentication.provider'))
            ->replaceArgument(0, new Reference($userProvider))
            ;

        $listenerId = 'security.authentication.listener.alb_oauth2_server.'.$id;
        $listener   = $container->setDefinition($listenerId, new DefinitionDecorator('alb.oauth2.server.security.authentication.listener'));

        return array($providerId, $listenerId, 'alb.oauth2.server.security.entry_point');
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
        return 'alb_oauth2';
    }

    /**
     * {@inheritdoc}
     */
    public function addConfiguration(NodeDefinition $node)
    {
    }
}
