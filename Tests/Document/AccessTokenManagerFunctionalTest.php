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

namespace FOS\OAuthServerBundle\Tests\Document;

use FOS\OAuthServerBundle\Document\TokenManager;
use FOS\OAuthServerBundle\Tests\Functional\TestBundle\Document\AccessToken;
use FOS\OAuthServerBundle\Tests\Functional\TestCase;

class AccessTokenManagerFunctionalTest extends TestCase
{
    /**
     * @var \FOS\OAuthServerBundle\Entity\AccessTokenManager
     */
    protected $accessTokenManager;

    /**
     * @var \Doctrine\ODM\MongoDB\DocumentManager
     */
    protected $documentManager;

    public function setUp(): void
    {
        parent::setUp();

        static::bootKernel(['environment' => 'odm']);

        $serviceContainer = self::$container->get('test.service_container');
        $this->documentManager = $serviceContainer->get('doctrine_mongodb')->getManager();
        $this->accessTokenManager = $serviceContainer->get('fos_oauth_server.access_token_manager.default');
    }

    public function tearDown(): void
    {
        $this->documentManager->getDocumentCollection(AccessToken::class)->deleteMany([]);

        unset($this->documentManager);

        parent::tearDown();
    }

    /**
     * Verify a single access token can be found by given token string.
     */
    public function testFindTokenByToken(): void
    {
        // set up two access tokens
        $expectedAccessToken = new AccessToken();
        $expectedAccessToken->setToken('expected-test-token');
        $this->documentManager->persist($expectedAccessToken);

        $unexpectedAccessToken = new AccessToken();
        $unexpectedAccessToken->setToken('unexpected-test-token');
        $this->documentManager->persist($unexpectedAccessToken);

        $this->documentManager->flush();

        // capture the persisted IDs
        $expectedAccessTokenId = $expectedAccessToken->getId();
        $unexpectedAccessTokenId = $unexpectedAccessToken->getId();

        // clear the new documents from doctrine memory
        $this->documentManager->clear();

        // confirm the expected access token is found
        $foundAccessToken = $this->accessTokenManager->findTokenByToken('expected-test-token');

        self::assertInstanceOf(AccessToken::class, $foundAccessToken);
        self::assertSame($expectedAccessTokenId, $foundAccessToken->getId());
        self::assertNotSame($unexpectedAccessTokenId, $foundAccessToken->getId());
    }

    /**
     * Verify an access token can be updated.
     */
    public function testUpdateTokenPersistsAndFlushes(): void
    {
        // set up an access token, but do not persist it
        $accessToken = new AccessToken();
        $accessToken->setToken('test-token');

        // update the access token
        $this->accessTokenManager->updateToken($accessToken);

        // confirm the access token is persisted and flushed
        $this->documentManager->clear();
        $foundAccessToken = $this->accessTokenManager->findTokenByToken('test-token');

        self::assertInstanceOf(AccessToken::class, $foundAccessToken);
        self::assertStringMatchesFormat('%s', $foundAccessToken->getId());
    }

    /**
     * Verify an access token can be removed.
     */
    public function testDeleteToken(): void
    {
        // set up an access token
        $accessToken = new AccessToken();
        $accessToken->setToken('test-token');
        $this->documentManager->persist($accessToken);
        $this->documentManager->flush();

        // remove the access token
        $this->accessTokenManager->deleteToken($accessToken);

        // confirm the access token can't be found
        $this->documentManager->clear();

        self::assertNull($this->accessTokenManager->findTokenByToken('test-token'));
    }

    /**
     * Verify all expired access tokens can be removed in one operation.
     */
    public function testDeleteExpired(): void
    {
        // set up an access token that expired one second ago
        $expiredAccessToken1 = new AccessToken();
        $expiredAccessToken1->setToken('expired-test-token-1');
        $expiredAccessToken1->setExpiresAt(time() - 1);
        $this->documentManager->persist($expiredAccessToken1);

        // set up an access token that expires in 10 seconds (avoid false failure
        // if the test host stalls)
        $unexpiredAccessToken = new AccessToken();
        $unexpiredAccessToken->setToken('unexpired-test-token');
        $unexpiredAccessToken->setExpiresAt(time() + 10);
        $this->documentManager->persist($unexpiredAccessToken);

        // set up another access token that expired
        $expiredAccessToken2 = new AccessToken();
        $expiredAccessToken2->setToken('expired-test-token-2');
        $expiredAccessToken2->setExpiresAt(time() - 10);
        $this->documentManager->persist($expiredAccessToken2);

        $this->documentManager->flush();

        // clear the new documents from doctrine memory
        $this->documentManager->clear();

        // delete all expired
        self::assertSame(2, $this->accessTokenManager->deleteExpired());

        // confirm only the unexpired access token is found
        self::assertNull($this->accessTokenManager->findTokenByToken('expired-test-token-1'));
        self::assertNull($this->accessTokenManager->findTokenByToken('expired-test-token-2'));
        self::assertInstanceOf(AccessToken::class, $this->accessTokenManager->findTokenByToken('unexpired-test-token'));
    }
}
