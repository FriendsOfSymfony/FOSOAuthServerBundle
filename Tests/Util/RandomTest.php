<?php

/*
 * This file is part of the FOSOAuthServerBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\OAuth2ServiceBundle\Tests\Util;

use FOS\OAuthServerBundle\Util\Random;

class OAuthRandomTest extends \PHPUnit_Framework_TestCase
{
    public function callPrivateMethod($obj, $name, array $args)
    {
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method->invokeArgs($obj, $args);
    }

    public function testHash()
    {
        $randomTest = new Random();
        $hash = $this->callPrivateMethod($randomTest, 'generateHash', array('test'));
        $this->assertSame($hash, hex2bin('9f86d081884c7d659a2feaa0c55ad015a3bf4f1b2b0b822cd15d6c15b0f00a08'));
    }

    public function testGenerateUniqueIdYeildsNoneNullResult()
    {
        $randomTest = new Random();
        $hash = $this->callPrivateMethod($randomTest, 'getUniqId', array());
        $this->assertNotNull($hash);
    }

    public function testUniqueIdYeildsStringOfAtLeastTheExpectedMinLength()
    {
        $randomTest = new Random();
        $hash = $this->callPrivateMethod($randomTest, 'getUniqId', array());

        // 23 figure from - http://php.net/manual/en/function.uniqid.php
        $this->assertGreaterThan(23, strlen($hash));
    }

    public function testBaseConversion()
    {
        $randomTest = new Random();
        $hash = $this->callPrivateMethod($randomTest, 'generateHash', array('test'));
        $result = $this->callPrivateMethod($randomTest, 'pseudoRandomBytesToBase', array($hash));

        $this->assertSame($result, '3z4xlzwqzv8kosoowsg48s48cccc8gocwowco8wggsg0o8owcg');
    }
}
