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

namespace FOS\OAuthServerBundle\Security\Authenticator\Passport\Badge;

use FOS\OAuthServerBundle\Model\AccessToken;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\BadgeInterface;

class AccessTokenBadge implements BadgeInterface
{
    private AccessToken $AccessToken;

    private array $roles;

    /**
     * AccessTokenBadge constructor.
     */
    public function __construct(AccessToken $AccessToken, array $roles)
    {
        $this->AccessToken = $AccessToken;
        $this->roles = $roles;
    }

    /**
     * {@inheritDoc}
     */
    public function isResolved(): bool
    {
        return !empty($this->roles);
    }

    public function getAccessToken(): AccessToken
    {
        return $this->AccessToken;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }
}
