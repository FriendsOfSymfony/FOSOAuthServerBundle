<?php

/*
 * This file is part of the FOSOAuthServerBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Loader\XmlFileLoader;

class FOSOAuthServerExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testLoadAuthorizeRouting()
    {
        $locator = new FileLocator();
        $loader = new XmlFileLoader($locator);

        $collection = $loader->load(__DIR__.'/../../Resources/config/routing/authorize.xml');
        $authorizeRoute = $collection->get('fos_oauth_server_authorize');
        $this->assertEquals('/oauth/v2/auth', $authorizeRoute->getPath());
        $this->assertEquals(array('GET', 'POST'), $authorizeRoute->getMethods());
    }

    public function testLoadTokenRouting()
    {
        $locator = new FileLocator();
        $loader = new XmlFileLoader($locator);

        $collection = $loader->load(__DIR__.'/../../Resources/config/routing/token.xml');
        $tokenRoute = $collection->get('fos_oauth_server_token');
        $this->assertEquals('/oauth/v2/token', $tokenRoute->getPath());
        $this->assertEquals(array('GET', 'POST'), $tokenRoute->getMethods());
    }
}
