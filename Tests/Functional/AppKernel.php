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

namespace FOS\OAuthServerBundle\Tests\Functional;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            new \FOS\OAuthServerBundle\FOSOAuthServerBundle(),
            new \FOS\OAuthServerBundle\Tests\Functional\TestBundle\TestBundle(),
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Symfony\Bundle\MonologBundle\MonologBundle(),
            new \Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new \Symfony\Bundle\TwigBundle\TwigBundle(),
        ];

        if ('orm' === $this->getEnvironment()) {
            $bundles[] = new \Doctrine\Bundle\DoctrineBundle\DoctrineBundle();
        } elseif ('odm' === $this->getEnvironment()) {
            $bundles[] = new \Doctrine\Bundle\MongoDBBundle\DoctrineMongoDBBundle();
        }

        return $bundles;
    }

    public function getCacheDir()
    {
        return sys_get_temp_dir().'/FOSOAuthServerBundle/';
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
    }
}
