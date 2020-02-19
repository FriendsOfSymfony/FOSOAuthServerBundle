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

namespace FOS\OAuthServerBundle\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use ReflectionClass;

class TestCase extends BaseTestCase
{
    /**
     * Assert sameness to the value of an object's private or protected member.
     *
     * @param mixed $expected
     * @param object $object
     * @param string $property
     */
    protected static function assertObjectPropertySame($expected, object $object, string $property): void
    {
        self::assertSame($expected, self::getProtectedMemberValue($object, $property));
    }

    /**
     * Get the value of an object's private or protected member.
     *
     * @param object $object
     * @param string $property
     *
     * @return mixed
     */
    protected static function getProtectedMemberValue(object $object, string $property)
    {
        $reflectionClass = new ReflectionClass($object);
        $reflectionProperty = $reflectionClass->getProperty($property);
        $reflectionProperty->setAccessible(true);

        return $reflectionProperty->getValue($object);
    }
}
