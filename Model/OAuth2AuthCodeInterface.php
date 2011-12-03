<?php
/*
 *
 */

namespace Alb\OAuth2ServerBundle\Model;

/**
 * @author Richard Fullmer <richard.fullmer@opensoftdev.com>
 */
interface OAuth2AuthCodeInterface
{
    function getId();

    function setExpiresAt($timestamp);

    function getExpiresAt();

    function setCode($code);

    function setScope($scope);

    function setData($data);

    function setRedirectUri($redirectUri);

    function getRedirectUri();

    function setClient(OAuth2ClientInterface $client);

    function getClient();
}
