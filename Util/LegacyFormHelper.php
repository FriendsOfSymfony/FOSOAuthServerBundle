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

use InvalidArgumentException;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use FOS\OAuthServerBundle\Form\Type\AuthorizeFormType;

/**
 * @internal
 *
 * @author Sanjay Pillai <sanjaypillai11@gmail.com>
 */
final class LegacyFormHelper
{
    /** @var array  */
    private static $map = [
        HiddenType::class => 'hidden',
        AuthorizeFormType::class => 'fos_oauth_server_authorize',
    ];

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    public static function getType(string $class)
    {
        if (!self::isLegacy()) {
            return $class;
        }

        if (!isset(self::$map[$class])) {
            throw new InvalidArgumentException(
                sprintf(
                    'Form type with class "%s" can not be found. '
                    . 'Please check for typos or add it to the map in LegacyFormHelper',
                    $class
                )
            );
        }

        return self::$map[$class];
    }

    public static function isLegacy(): bool
    {
        return !method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix');
    }
}
