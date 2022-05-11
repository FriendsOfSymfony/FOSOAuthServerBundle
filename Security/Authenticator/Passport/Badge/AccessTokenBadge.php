<?php


namespace FOS\OAuthServerBundle\Security\Authenticator\Passport\Badge;


use FOS\OAuthServerBundle\Model\AccessToken;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\BadgeInterface;

class AccessTokenBadge implements BadgeInterface
{
    /**
     * @var AccessToken
     */
    private $AccessToken;

    /**
     * @var array
     */
    private $roles;

    /**
     * AccessTokenBadge constructor.
     * @param AccessToken $AccessToken
     * @param array $roles
     */
    public function __construct( AccessToken $AccessToken, array $roles )
    {
        $this->AccessToken = $AccessToken;
        $this->roles = $roles;
    }

    /**
     * @inheritDoc
     */
    public function isResolved(): bool
    {
        return ! empty ( $this->roles );
    }

    /**
     * @return AccessToken
     */
    public function getAccessToken(): AccessToken
    {
        return $this->AccessToken;
    }

    /**
     * @return array
     */
    public function getRoles(): array
    {
        return $this->roles;
    }
}