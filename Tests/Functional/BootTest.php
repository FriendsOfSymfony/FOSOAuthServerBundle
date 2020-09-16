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

use Exception;

class BootTest extends TestCase
{
    /**
     * @dataProvider getTestBootData
     *
     * @param string $env
     */
    public function testBoot($env): void
    {
        try {
            $kernel = static::createKernel(['environment' => $env]);
            $kernel->boot();

            // no exceptions were thrown
            self::assertTrue(true);
        } catch (Exception $exception) {
            $this->fail($exception->getMessage());
        }
    }

    public function getTestBootData(): array
    {
        return [
            'booting the kernel with SQL configurations' => ['env' => 'orm'],
            'booting the kernel with Mongo configurations' => ['env' => 'odm'],
        ];
    }
}
