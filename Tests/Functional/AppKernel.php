<?php

namespace FOS\OAuthServerBundle\Tests\Functional;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new \FOS\OAuthServerBundle\FOSOAuthServerBundle(),

            new \FOS\OAuthServerBundle\Tests\Functional\TestBundle\TestBundle(),
        );

        if ('orm' == $this->getEnvironment()) {
            $bundles[] = new \Doctrine\Bundle\DoctrineBundle\DoctrineBundle();
        }

        return $bundles;
    }

    public function getCacheDir()
    {
        return sys_get_temp_dir().'/FOSOAuthServerBundle/';
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
    }
}
