<?php

/*
 * This file is part of the FOSOAuthServerBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\OAuthServerBundle\Util;

class Random
{
    /**
     * @param bool $strong Whether to use a cryptographically strong algorithm to produce the pseudo-random bytes.
     */
    public static function generateToken($strong = false)
    {
        $bytes = false;
        if ($strong && function_exists('openssl_random_pseudo_bytes') && 0 !== stripos(PHP_OS, 'win')) {
            $bytes = openssl_random_pseudo_bytes(32, $strong);
        }

        // let's just hope we got a good seed
        if (false === $bytes) {
            $bytes = hash('sha256', uniqid(mt_rand(), true), true);
        }

        return base_convert(bin2hex($bytes), 16, 36);
    }
}
