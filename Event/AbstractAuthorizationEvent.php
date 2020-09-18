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

namespace FOS\OAuthServerBundle\Event;

use FOS\OAuthServerBundle\Model\ClientInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\EventDispatcher\Event;

class AbstractAuthorizationEvent extends Event
{
    /**
     * @var UserInterface
     */
    private $user;

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var bool
     */
    private $isAuthorizedClient;

    public function __construct(UserInterface $user, ClientInterface $client, bool $isAuthorizedClient = false)
    {
        $this->user = $user;
        $this->client = $client;
        $this->isAuthorizedClient = $isAuthorizedClient;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function setAuthorizedClient(bool $authorized)
    {
        $this->isAuthorizedClient = $authorized;
    }

    public function isAuthorizedClient(): bool
    {
        return $this->isAuthorizedClient;
    }

    public function getClient(): ClientInterface
    {
        return $this->client;
    }
}
