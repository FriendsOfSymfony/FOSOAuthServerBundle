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

use FOS\OAuthServerBundle\Tests\Functional\TestBundle\Document\AuthCode;
use FOS\OAuthServerBundle\Tests\Functional\TestCase;

class AuthCodeManagerFunctionalTest extends TestCase
{
    /**
     * @var \FOS\OAuthServerBundle\Document\AuthCodeManager
     */
    protected $authCodeManager;

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
        $this->authCodeManager = $serviceContainer->get('fos_oauth_server.auth_code_manager.default');
    }

    public function tearDown(): void
    {
        $this->documentManager->getDocumentCollection(AuthCode::class)->deleteMany([]);

        unset($this->documentManager);

        parent::tearDown();
    }

    /**
     * Verify a single auth code can be found by given criteria.
     */
    public function testFindAuthCodeBy(): void
    {
        // set up two auth codes
        $expectedAuthCode = new AuthCode();
        $expectedAuthCode->setToken('expected-test-token');
        $this->documentManager->persist($expectedAuthCode);

        $unexpectedAuthCode = new AuthCode();
        $unexpectedAuthCode->setToken('unexpected-test-token');
        $this->documentManager->persist($unexpectedAuthCode);

        $this->documentManager->flush();

        // capture the persisted IDs
        $expectedAuthCodeId = $expectedAuthCode->getId();
        $unexpectedAuthCodeId = $unexpectedAuthCode->getId();

        // clear the new documents from doctrine memory
        $this->documentManager->clear();

        // confirm the expected auth code is found
        $foundAuthCode = $this->authCodeManager->findAuthCodeBy(['token' => 'expected-test-token']);

        self::assertInstanceOf(AuthCode::class, $foundAuthCode);
        self::assertSame($expectedAuthCodeId, $foundAuthCode->getId());
        self::assertNotSame($unexpectedAuthCodeId, $foundAuthCode->getId());
    }

    /**
     * Verify an auth code can be updated.
     */
    public function testUpdateAuthCode(): void
    {
        // set up an auth code, but do not persist it
        $authCode = new AuthCode();
        $authCode->setToken('test-token');

        // update the auth code
        $this->authCodeManager->updateAuthCode($authCode);

        // confirm the auth code is persisted and flushed
        $this->documentManager->clear();
        $foundAuthCode = $this->authCodeManager->findAuthCodeBy(['token' => 'test-token']);

        self::assertInstanceOf(AuthCode::class, $foundAuthCode);
        self::assertStringMatchesFormat('%s', $foundAuthCode->getId());
    }

    /**
     * Verify an auth code can be removed.
     */
    public function testDeleteAuthCode(): void
    {
        // set up an auth code
        $authCode = new AuthCode();
        $authCode->setToken('test-token');
        $this->documentManager->persist($authCode);
        $this->documentManager->flush();

        // remove the auth code
        $this->authCodeManager->deleteAuthCode($authCode);

        // confirm the auth code can't be found
        $this->documentManager->clear();

        self::assertNull($this->authCodeManager->findAuthCodeBy(['token' => 'test-token']));
    }

    /**
     * Verify all expired auth codes can be removed in one operation.
     */
    public function testDeleteExpired(): void
    {
        // set up an auth code that expired one second ago
        $expiredAuthCode1 = new AuthCode();
        $expiredAuthCode1->setExpiresAt(time() - 1);
        $this->documentManager->persist($expiredAuthCode1);

        // set up an auth code that expires in 10 seconds (avoid false failure
        // if the test host stalls)
        $unexpiredAuthCode = new AuthCode();
        $unexpiredAuthCode->setExpiresAt(time() + 10);
        $this->documentManager->persist($unexpiredAuthCode);

        // set up another auth code that expired
        $expiredAuthCode2 = new AuthCode();
        $expiredAuthCode2->setExpiresAt(time() - 10);
        $this->documentManager->persist($expiredAuthCode2);

        $this->documentManager->flush();

        // capture the persisted IDs
        $expiredAuthCodeId1 = $expiredAuthCode1->getId();
        $expiredAuthCodeId2 = $expiredAuthCode2->getId();
        $unexpiredAuthCodeId = $unexpiredAuthCode->getId();

        // clear the new documents from doctrine memory
        $this->documentManager->clear();

        // delete all expired
        self::assertSame(2, $this->authCodeManager->deleteExpired());

        // confirm only the unexpired auth code is found
        self::assertNull($this->authCodeManager->findAuthCodeBy(['id' => $expiredAuthCodeId1]));
        self::assertNull($this->authCodeManager->findAuthCodeBy(['id' => $expiredAuthCodeId2]));
        self::assertInstanceOf(AuthCode::class, $this->authCodeManager->findAuthCodeBy(['id' => $unexpiredAuthCodeId]));
    }
}
