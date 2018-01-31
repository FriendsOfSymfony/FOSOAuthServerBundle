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

namespace FOS\OAuthServerBundle\Util;

/**
 * Class Random.
 *
 * @author Nikola Petkanski <nikola@petkanski.com
 */
class Random
{
    public static function generateToken()
    {
        $bytes = random_bytes(32);

        return base_convert(bin2hex($bytes), 16, 36);
    }
}
