<?php

/*
 * This file is part of the FOSOAuthServerBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\OAuthServerBundle;

use FOS\OAuthServerBundle\DependencyInjection\FOSOAuthServerExtension;
use FOS\OAuthServerBundle\DependencyInjection\Security\Factory\OAuthFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class FOSOAuthServerBundle extends Bundle
{
    public function __construct()
    {
        $this->extension = new FOSOAuthServerExtension();
    }

    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $extension = $container->getExtension('security');
        if (method_exists($extension, 'addSecurityListenerFactory')) {
            $extension->addSecurityListenerFactory(new OAuthFactory);
        }
    }
}
