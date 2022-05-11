<?php


namespace FOS\OAuthServerBundle\Security\Authenticator;


use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportTrait;

class OAuth2Passport implements PassportInterface
{
    use PassportTrait;
}