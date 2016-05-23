<?php

/*
 * This file is part of the FOSOAuthServerBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\OAuthServerBundle\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;

abstract class TestCase extends WebTestCase
{
    protected static function createKernel(array $options = array())
    {
        $env = @$options['env'] ?: 'test';

        return new AppKernel($env, true);
    }

    protected function setUp()
    {
        $fs = new Filesystem();
        $fs->remove(sys_get_temp_dir().'/FOSOAuthServerBundle/');
    }

    protected function tearDown()
    {
        static::$kernel = null;
    }
}
