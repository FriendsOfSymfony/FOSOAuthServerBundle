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

use FOS\OAuthServerBundle\Tests\Functional\TestBundle\Document\RefreshToken;
use FOS\OAuthServerBundle\Tests\Functional\TestCase;

class RefreshTokenManagerFunctionalTest extends TestCase
{
    /**
     * @var \FOS\OAuthServerBundle\Entity\RefreshTokenManager
     */
    protected $refreshTokenManager;

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
        $this->refreshTokenManager = $serviceContainer->get('fos_oauth_server.refresh_token_manager.default');
    }

    public function tearDown(): void
    {
        $this->documentManager->getDocumentCollection(RefreshToken::class)->deleteMany([]);

        unset($this->documentManager);

        parent::tearDown();
    }

    /**
     * Verify a single refresh token can be found by given token string.
     */
    public function testFindTokenByToken(): void
    {
        // set up two refresh tokens
        $expectedRefreshToken = new RefreshToken();
        $expectedRefreshToken->setToken('expected-test-token');
        $this->documentManager->persist($expectedRefreshToken);

        $unexpectedRefreshToken = new RefreshToken();
        $unexpectedRefreshToken->setToken('unexpected-test-token');
        $this->documentManager->persist($unexpectedRefreshToken);

        $this->documentManager->flush();

        // capture the persisted IDs
        $expectedRefreshTokenId = $expectedRefreshToken->getId();
        $unexpectedRefreshTokenId = $unexpectedRefreshToken->getId();

        // clear the new documents from doctrine memory
        $this->documentManager->clear();

        // confirm the expected refresh token is found
        $foundRefreshToken = $this->refreshTokenManager->findTokenByToken('expected-test-token');

        self::assertInstanceOf(RefreshToken::class, $foundRefreshToken);
        self::assertSame($expectedRefreshTokenId, $foundRefreshToken->getId());
        self::assertNotSame($unexpectedRefreshTokenId, $foundRefreshToken->getId());
    }

    /**
     * Verify an refresh token can be updated.
     */
    public function testUpdateTokenPersistsAndFlushes(): void
    {
        // set up an refresh token, but do not persist it
        $refreshToken = new RefreshToken();
        $refreshToken->setToken('test-token');

        // update the refresh token
        $this->refreshTokenManager->updateToken($refreshToken);

        // confirm the refresh token is persisted and flushed
        $this->documentManager->clear();
        $foundRefreshToken = $this->refreshTokenManager->findTokenByToken('test-token');

        self::assertInstanceOf(RefreshToken::class, $foundRefreshToken);
        self::assertStringMatchesFormat('%s', $foundRefreshToken->getId());
    }

    /**
     * Verify an refresh token can be removed.
     */
    public function testDeleteToken(): void
    {
        // set up an refresh token
        $refreshToken = new RefreshToken();
        $refreshToken->setToken('test-token');
        $this->documentManager->persist($refreshToken);
        $this->documentManager->flush();

        // remove the refresh token
        $this->refreshTokenManager->deleteToken($refreshToken);

        // confirm the refresh token can't be found
        $this->documentManager->clear();

        self::assertNull($this->refreshTokenManager->findTokenByToken('test-token'));
    }

    /**
     * Verify all expired refresh tokens can be removed in one operation.
     */
    public function testDeleteExpired(): void
    {
        // set up an refresh token that expired one second ago
        $expiredRefreshToken1 = new RefreshToken();
        $expiredRefreshToken1->setToken('expired-test-token-1');
        $expiredRefreshToken1->setExpiresAt(time() - 1);
        $this->documentManager->persist($expiredRefreshToken1);

        // set up an refresh token that expires in 10 seconds (avoid false failure
        // if the test host stalls)
        $unexpiredRefreshToken = new RefreshToken();
        $unexpiredRefreshToken->setToken('unexpired-test-token');
        $unexpiredRefreshToken->setExpiresAt(time() + 10);
        $this->documentManager->persist($unexpiredRefreshToken);

        // set up another refresh token that expired
        $expiredRefreshToken2 = new RefreshToken();
        $expiredRefreshToken2->setToken('expired-test-token-2');
        $expiredRefreshToken2->setExpiresAt(time() - 10);
        $this->documentManager->persist($expiredRefreshToken2);

        $this->documentManager->flush();

        // clear the new documents from doctrine memory
        $this->documentManager->clear();

        // delete all expired
        self::assertSame(2, $this->refreshTokenManager->deleteExpired());

        // confirm only the unexpired refresh token is found
        self::assertNull($this->refreshTokenManager->findTokenByToken('expired-test-token-1'));
        self::assertNull($this->refreshTokenManager->findTokenByToken('expired-test-token-2'));
        self::assertInstanceOf(RefreshToken::class, $this->refreshTokenManager->findTokenByToken('unexpired-test-token'));
    }
}
