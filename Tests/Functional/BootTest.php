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

class BootTest extends TestCase
{
    /**
     * @dataProvider getTestBootData
     */
    public function testBoot($env)
    {
        $this->markTestIncomplete('Issue with Stopwatch component');

        $kernel = $this->createKernel(array('env' => $env));
        $kernel->boot();
    }

    public function getTestBootData()
    {
        return array(
            array('orm'),
        );
    }
}
