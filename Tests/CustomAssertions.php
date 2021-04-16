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

use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;

trait CustomAssertions
{
    use ArraySubsetAsserts;

    /**
     * Replacement for assert removed in PHPUnit 9.
     *
     * @param mixed  $expected
     * @param object $actualObject
     */
    public static function assertAttributeSame($expected, string $actualAttributeName, $actualObject, string $message = ''): void
    {
        $prop = new \ReflectionProperty(\get_class($actualObject), $actualAttributeName);

        $prop->setAccessible(true);
        self::assertSame($expected, $prop->getValue($actualObject));
    }
}
