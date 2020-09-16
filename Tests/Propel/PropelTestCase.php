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

namespace FOS\OAuthServerBundle\Tests\Propel;

use FOS\OAuthServerBundle\Tests\TestCase;
use Propel;

class PropelTestCase extends TestCase
{
    public function setUp(): void
    {
        if (!class_exists(Propel::class)) {
            $this->markTestSkipped('Propel is not installed.');
        }
    }
}
