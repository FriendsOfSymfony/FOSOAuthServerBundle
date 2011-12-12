<?php

namespace Alb\OAuth2ServerBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Alb\OAuth2ServerBundle\DependencyInjection\Security\Factory\OAuth2Factory;

class AlbOAuth2ServerBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $extension = $container->getExtension('security');
        if (method_exists($extension, 'addSecurityListenerFactory')) {
            $extension->addSecurityListenerFactory(new OAuth2Factory);
        }
    }
}
