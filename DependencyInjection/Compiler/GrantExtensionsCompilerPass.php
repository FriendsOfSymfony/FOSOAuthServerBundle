<?php

namespace FOS\OAuthServerBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

/**
 * @author Adrien Brault <adrien.brault@gmail.com>
 */
class GrantExtensionsCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $storageDefinition = $container->findDefinition('fos_oauth_server.storage');

        // if the fos_oauth_server.storage is overloaded ->getClass() return the given class from services.xml like %class_name%
        if(strpos($storageDefinition->getClass(), '%') === false)
            $storageClass = new \ReflectionClass($storageDefinition->getClass());
        else
            // get the class via param before replace % character
            $storageClass = new \ReflectionClass($container->getParameter(str_replace ('%', '', $storageDefinition->getClass ())));

        if (!$storageClass->implementsInterface('FOS\OAuthServerBundle\Storage\GrantExtensionDispatcherInterface')) {
            return;
        }

        foreach ($container->findTaggedServiceIds('fos_oauth_server.grant_extension') as $id => $tags) {
            foreach ($tags as $tag) {
                if (empty($tag['uri'])) {
                    throw new InvalidArgumentException(sprintf('Service "%s" must define the "uri" attribute on "fos_oauth_server.grant_extension" tags.', $id));
                }

                $storageDefinition->addMethodCall('setGrantExtension', array($tag['uri'], new Reference($id)));
            }
        }
    }
}
