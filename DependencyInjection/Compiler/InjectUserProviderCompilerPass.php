<?php

/*
 * This file is part of the FOSOAuthServerBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\OAuthServerBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

/**
 * @author Thomas Prelot <tprelot@gmail.com>
 */
class InjectUserProviderCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $storageDefinition = $container->findDefinition('fos_oauth_server.storage');

        if (null !== $userProvider = $container->getParameter('fos_oauth_server.user_provider.service')) {
            $storageDefinition->replaceArgument(4, new Reference($userProvider));
        }
    }
}
