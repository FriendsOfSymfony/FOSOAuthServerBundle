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
    private static function getUniqId()
    {
        return uniqid(mt_rand(), true);
    }

    private static function generateHash($uniqId)
    {
        return hash('sha256', $uniqId, true);
    }

    private static function openSSLAvailable()
    {
        return function_exists('openssl_random_pseudo_bytes') && 0 !== stripos(PHP_OS, 'win');
    }

    private static function generatePseudoRandomBytes()
    {
        $bytes = openssl_random_pseudo_bytes(32, $strong);

        if (true !== $strong) {
            $bytes = false;
        }

        return $bytes;
    }

    private static function pseudoRandomBytesToBase($bytes)
    {
        return base_convert(bin2hex($bytes), 16, 36);
    }

    public static function generateToken()
    {
        $bytes = false;

        if (self::openSSLAvailable()) {
            $bytes = self::generatePseudoRandomBytes();
        }

        // let's just hope we got a good seed
        if (false === $bytes) {
            $uniqId = self::getUniqId();
            $bytes = self::generateHash($uniqId);
        }

        return self::pseudoRandomBytesToBase($bytes);
    }
}
