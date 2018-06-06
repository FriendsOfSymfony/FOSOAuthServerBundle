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

namespace FOS\OAuthServerBundle\Storage;

use Symfony\Component\Security\Core\User\UserInterface;

interface PasswordCheckerInterface
{
    /**
     * Validate the user's password matches
     *
     * @param  UserInterface $user
     * @param  string        $password
     * @return bool
     */
    public function validate(UserInterface $user, $password): bool;
}
