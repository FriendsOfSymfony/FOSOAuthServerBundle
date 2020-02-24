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

namespace FOS\OAuthServerBundle\Tests\Functional\TestBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use FOS\OAuthServerBundle\Document\AuthCode as BaseAuthCode;
use FOS\OAuthServerBundle\Model\ClientInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @MongoDB\Document(
 *     db="fos_oauth_server_test",
 *     collection="auth_code"
 * )
 */
class AuthCode extends BaseAuthCode
{
    /**
     * @var string
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @var int
     * @MongoDB\Field(type="int")
     */
    protected $expiresAt;

    /**
     * @var string
     * @MongoDB\Field(type="string")
     */
    protected $scope;

    /**
     * @var string
     * @MongoDB\Field(type="string")
     */
    protected $token;

    /**
     * @var ClientInterface
     * @MongoDB\EmbedOne(targetDocument="FOS\OAuthServerBundle\Tests\Functional\TestBundle\Document\Client")
     */
    protected $client;

    /**
     * @var UserInterface
     * @MongoDB\EmbedOne(targetDocument="FOS\OAuthServerBundle\Tests\Functional\TestBundle\Document\User")
     */
    protected $user;
}
