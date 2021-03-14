<?php

/*
 * This file is part of the FOSOAuthServerBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\OAuth2ServiceBundle\Tests\Storage;

use FOS\OAuthServerBundle\Model\AccessToken;
use FOS\OAuthServerBundle\Model\AccessTokenManagerInterface;
use FOS\OAuthServerBundle\Model\AuthCode;
use FOS\OAuthServerBundle\Model\AuthCodeInterface;
use FOS\OAuthServerBundle\Model\AuthCodeManagerInterface;
use FOS\OAuthServerBundle\Model\Client;
use FOS\OAuthServerBundle\Model\ClientManagerInterface;
use FOS\OAuthServerBundle\Model\RefreshToken;
use FOS\OAuthServerBundle\Model\RefreshTokenManagerInterface;
use FOS\OAuthServerBundle\Storage\GrantExtensionInterface;
use FOS\OAuthServerBundle\Storage\OAuthStorage;
use OAuth2\Model\IOAuth2Client;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class OAuthStorageTest extends TestCase
{
    protected $clientManager;

    protected $accessTokenManager;

    protected $refreshTokenManager;

    protected $authCodeManager;

    protected $userProvider;

    protected $encoderFactory;

    protected $storage;

    public function setUp(): void
    {
        $this->clientManager = $this->getMockBuilder(ClientManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->accessTokenManager = $this->getMockBuilder(AccessTokenManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->refreshTokenManager = $this->getMockBuilder(RefreshTokenManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->authCodeManager = $this->getMockBuilder(AuthCodeManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->userProvider = $this->getMockBuilder(UserProviderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->encoderFactory = $this->getMockBuilder(EncoderFactoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storage = new OAuthStorage(
            $this->clientManager,
            $this->accessTokenManager,
            $this->refreshTokenManager,
            $this->authCodeManager,
            $this->userProvider,
            $this->encoderFactory
        );
    }

    public function testGetClientReturnsClientWithGivenId(): void
    {
        $client = new Client();

        $this->clientManager->expects(self::once())
            ->method('findClientByPublicId')
            ->with('123_abc')
            ->willReturn($client);

        self::assertSame($client, $this->storage->getClient('123_abc'));
    }

    public function testGetClientReturnsNullIfNotExists(): void
    {
        $this->clientManager->expects(self::once())
            ->method('findClientByPublicId')
            ->with('123_abc')
            ->willReturn(null);

        self::assertNull($this->storage->getClient('123_abc'));
    }

    public function testCheckClientCredentialsThrowsIfInvalidClientClass(): void
    {
        $client = $this->getMockBuilder(IOAuth2Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->expectException('InvalidArgumentException');
        $this->storage->checkClientCredentials($client, 'dummy');
    }

    public function testCheckClientCredentialsReturnsTrueOnValidCredentials(): void
    {
        $client = new Client();
        $client->setSecret('dummy');

        self::assertTrue($this->storage->checkClientCredentials($client, 'dummy'));
    }

    public function testCheckClientCredentialsReturnsFalseOnValidCredentials(): void
    {
        $client = new Client();
        $client->setSecret('dummy');

        self::assertFalse($this->storage->checkClientCredentials($client, 'passe'));
    }

    public function testGetAccessTokenReturnsAccessTokenWithGivenId(): void
    {
        $token = new AccessToken();

        $this->accessTokenManager->expects(self::once())
            ->method('findTokenByToken')
            ->with('123_abc')
            ->willReturn($token);

        self::assertSame($token, $this->storage->getAccessToken('123_abc'));
    }

    public function testGetAccessTokenReturnsNullIfNotExists(): void
    {
        $this->accessTokenManager->expects(self::once())
            ->method('findTokenByToken')
            ->with('123_abc')
            ->willReturn(null);

        self::assertNull($this->storage->getAccessToken('123_abc'));
    }

    public function testCreateAccessTokenThrowsOnInvalidClientClass(): void
    {
        $client = $this->getMockBuilder(IOAuth2Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->expectException('InvalidArgumentException');
        $this->storage->createAccessToken('foo', $client, new User(42), 1, 'foo bar');
    }

    public function testCreateAccessToken(): void
    {
        $savedToken = null;

        $this->accessTokenManager->expects(self::once())
            ->method('createToken')
            ->with()
            ->willReturn(new AccessToken());
        $this->accessTokenManager->expects(self::once())
            ->method('updateToken')
            ->willReturnCallback(
                function ($token) use (&$savedToken) {
                    $savedToken = $token;
                }
            );

        $client = new Client();
        $user = new User(42);

        $token = $this->storage->createAccessToken('foo', $client, $user, 1, 'foo bar');

        self::assertEquals($token, $savedToken);

        self::assertSame('foo', $token->getToken());
        self::assertSame($client, $token->getClient());
        self::assertSame($user, $token->getData());
        self::assertSame($user, $token->getUser());
        self::assertSame(1, $token->getExpiresAt());
        self::assertSame('foo bar', $token->getScope());
    }

    public function testCreateAccessTokenWithoutUser(): void
    {
        $savedToken = null;

        $this->accessTokenManager->expects(self::once())
            ->method('createToken')
            ->with()
            ->willReturn(new AccessToken());
        $this->accessTokenManager->expects(self::once())
            ->method('updateToken')
            ->willReturnCallback(
                function ($token) use (&$savedToken) {
                    $savedToken = $token;
                }
            );

        $client = new Client();
        $user = null;

        $token = $this->storage->createAccessToken('foo', $client, $user, 1, 'foo bar');

        self::assertEquals($token, $savedToken);
    }

    public function testGetRefreshTokenReturnsRefreshTokenWithGivenId(): void
    {
        $token = new RefreshToken();

        $this->refreshTokenManager->expects(self::once())
            ->method('findTokenByToken')
            ->with('123_abc')
            ->willReturn($token);

        self::assertSame($token, $this->storage->getRefreshToken('123_abc'));
    }

    public function testGetRefreshTokenReturnsNullIfNotExists(): void
    {
        $this->refreshTokenManager->expects(self::once())
            ->method('findTokenByToken')
            ->with('123_abc')
            ->willReturn(null);

        self::assertNull($this->storage->getRefreshToken('123_abc'));
    }

    public function testCreateRefreshTokenThrowsOnInvalidClientClass(): void
    {
        $client = $this->getMockBuilder(IOAuth2Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->expectException('InvalidArgumentException');
        $this->storage->createRefreshToken('foo', $client, 42, 1, 'foo bar');
    }

    public function testCreateRefreshToken(): void
    {
        $savedToken = null;

        $this->refreshTokenManager->expects(self::once())
            ->method('createToken')
            ->with()
            ->willReturn(new RefreshToken());
        $this->refreshTokenManager->expects(self::once())
            ->method('updateToken')
            ->willReturnCallback(
                function ($token) use (&$savedToken) {
                    $savedToken = $token;
                }
            );

        $client = new Client();
        $user = new User(42);

        $token = $this->storage->createRefreshToken('foo', $client, $user, 1, 'foo bar');

        self::assertEquals($token, $savedToken);

        self::assertSame('foo', $token->getToken());
        self::assertSame($client, $token->getClient());
        self::assertSame($user, $token->getData());
        self::assertSame($user, $token->getUser());
        self::assertSame(1, $token->getExpiresAt());
        self::assertSame('foo bar', $token->getScope());
    }

    public function testCreateRefreshTokenWithoutUser(): void
    {
        $savedToken = null;

        $this->refreshTokenManager->expects(self::once())
            ->method('createToken')
            ->with()
            ->willReturn(new RefreshToken());
        $this->refreshTokenManager->expects(self::once())
            ->method('updateToken')
            ->willReturnCallback(
                function ($token) use (&$savedToken) {
                    $savedToken = $token;
                }
            );

        $client = new Client();
        $user = null;

        $token = $this->storage->createRefreshToken('foo', $client, $user, 1, 'foo bar');

        self::assertEquals($token, $savedToken);
    }

    public function testCheckRestrictedGrantTypeThrowsOnInvalidClientClass(): void
    {
        $client = $this->getMockBuilder(IOAuth2Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->expectException('InvalidArgumentException');

        $this->storage->checkRestrictedGrantType($client, 'foo');
    }

    public function testCheckRestrictedGrantType(): void
    {
        $client = new Client();
        $client->setAllowedGrantTypes(array('foo', 'bar'));

        self::assertTrue($this->storage->checkRestrictedGrantType($client, 'foo'));
        self::assertTrue($this->storage->checkRestrictedGrantType($client, 'bar'));
        self::assertFalse($this->storage->checkRestrictedGrantType($client, 'baz'));
    }

    public function testCheckUserCredentialsThrowsOnInvalidClientClass(): void
    {
        $client = $this->getMockBuilder(IOAuth2Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->expectException('InvalidArgumentException');

        $this->storage->checkUserCredentials($client, 'Joe', 'baz');
    }

    public function testCheckUserCredentialsCatchesAuthenticationExceptions(): void
    {
        $client = new Client();

        $result = $this->storage->checkUserCredentials($client, 'Joe', 'baz');

        self::assertFalse($result);
    }

    public function testCheckUserCredentialsReturnsTrueOnValidCredentials(): void
    {
        $client = new Client();
        $user = $this->getMockBuilder(UserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $user->expects(self::once())
            ->method('getPassword')->with()->willReturn('foo');
        $user->expects(self::once())
            ->method('getSalt')->with()->willReturn('bar');

        $encoder = $this->getMockBuilder(PasswordEncoderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $encoder->expects(self::once())
            ->method('isPasswordValid')
            ->with('foo', 'baz', 'bar')
            ->willReturn(true);

        $this->userProvider->expects(self::once())
            ->method('loadUserByUsername')
            ->with('Joe')
            ->willReturn($user);

        $this->encoderFactory->expects(self::once())
            ->method('getEncoder')
            ->with($user)
            ->willReturn($encoder);

        self::assertSame(
            array(
                'data' => $user,
            ),
            $this->storage->checkUserCredentials($client, 'Joe', 'baz')
        );
    }

    public function testCheckUserCredentialsReturnsFalseOnInvalidCredentials(): void
    {
        $client = new Client();
        $user = $this->getMockBuilder(UserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $user->expects(self::once())
            ->method('getPassword')->with()->willReturn('foo');
        $user->expects(self::once())
            ->method('getSalt')->with()->willReturn('bar');

        $encoder = $this->getMockBuilder(PasswordEncoderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $encoder->expects(self::once())
            ->method('isPasswordValid')
            ->with('foo', 'baz', 'bar')
            ->willReturn(false);

        $this->userProvider->expects(self::once())
            ->method('loadUserByUsername')
            ->with('Joe')
            ->willReturn($user);

        $this->encoderFactory->expects(self::once())
            ->method('getEncoder')
            ->with($user)
            ->willReturn($encoder);

        self::assertFalse($this->storage->checkUserCredentials($client, 'Joe', 'baz'));
    }

    public function testCheckUserCredentialsReturnsFalseIfUserNotExist(): void
    {
        $client = new Client();

        $this->userProvider->expects(self::once())
            ->method('loadUserByUsername')
            ->with('Joe')
            ->willReturn(null);

        self::assertFalse($this->storage->checkUserCredentials($client, 'Joe', 'baz'));
    }

    public function testCreateAuthCodeThrowsOnInvalidClientClass(): void
    {
        $client = $this->getMockBuilder(IOAuth2Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->expectException('InvalidArgumentException');
        $this->storage->createAuthCode('foo', $client, 42, 'http://www.example.com/', 1, 'foo bar');
    }

    public function testCreateAuthCode(): void
    {
        $savedCode = null;

        $this->authCodeManager->expects(self::once())
            ->method('createAuthCode')
            ->with()
            ->willReturn(new AuthCode());
        $this->authCodeManager->expects(self::once())
            ->method('updateAuthCode')
            ->willReturnCallback(
                function ($code) use (&$savedCode) {
                    $savedCode = $code;
                }
            );

        $client = new Client();
        $user = new User(42);

        $code = $this->storage->createAuthCode('foo', $client, $user, 'http://www.example.com/', 1, 'foo bar');

        self::assertEquals($code, $savedCode);

        self::assertSame('foo', $code->getToken());
        self::assertSame($client, $code->getClient());
        self::assertSame($user, $code->getData());
        self::assertSame($user, $code->getUser());
        self::assertSame(1, $code->getExpiresAt());
        self::assertSame('foo bar', $code->getScope());
    }

    public function testGetAuthCodeReturnsAuthCodeWithGivenId(): void
    {
        $code = new AuthCode();

        $this->authCodeManager->expects(self::once())
            ->method('findAuthCodeByToken')
            ->with('123_abc')
            ->willReturn($code);

        self::assertSame($code, $this->storage->getAuthCode('123_abc'));
    }

    public function testGetAuthCodeReturnsNullIfNotExists(): void
    {
        $this->authCodeManager->expects(self::once())
            ->method('findAuthCodeByToken')
            ->with('123_abc')
            ->willReturn(null);

        self::assertNull($this->storage->getAuthCode('123_abc'));
    }

    public function testValidGrantExtension(): void
    {
        $grantExtension = $this->getMockBuilder(GrantExtensionInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $grantExtension
            ->expects(self::once())
            ->method('checkGrantExtension')
            ->willReturn(true);
        $this->storage->setGrantExtension('https://friendsofsymfony.com/grants/foo', $grantExtension);

        $client = $this->getMockBuilder(IOAuth2Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        self::assertTrue($this->storage->checkGrantExtension($client, 'https://friendsofsymfony.com/grants/foo', array(), array()));
    }

    /**
     * @expectedException \OAuth2\OAuth2ServerException
     */
    public function testInvalidGrantExtension(): void
    {
        $client = $this->getMockBuilder(IOAuth2Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storage->checkGrantExtension($client, 'https://friendsofsymfony.com/grants/bar', array(), array());
    }

    public function testDoubleSetGrantExtension(): void
    {
        $grantExtension = $this->getMockBuilder(GrantExtensionInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $grantExtension2 = $this->getMockBuilder(GrantExtensionInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storage->setGrantExtension($uri = 'https://friendsofsymfony.com/grants/foo', $grantExtension);
        $this->storage->setGrantExtension($uri, $grantExtension2);

        $storageClass = new \ReflectionClass(get_class($this->storage));
        $grantExtensionsProperty = $storageClass->getProperty('grantExtensions');
        $grantExtensionsProperty->setAccessible(true);
        $grantExtensions = $grantExtensionsProperty->getValue($this->storage);

        self::assertEquals($grantExtension2, $grantExtensions[$uri]);
    }

    public function testMarkAuthCodeAsUsedIfAuthCodeFound(): void
    {
        $authCode = $this->getMockBuilder(AuthCodeInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->authCodeManager->expects(self::atLeastOnce())
            ->method('findAuthCodeByToken')
            ->with('123_abc')
            ->willReturn($authCode);

        $this->authCodeManager->expects(self::atLeastOnce())
            ->method('deleteAuthCode')
            ->with($authCode)
            ->willReturn(null);

        $this->storage->markAuthCodeAsUsed('123_abc');
    }

    public function testMarkAuthCodeAsUsedIfAuthCodeNotFound(): void
    {
        $this->authCodeManager->expects(self::atLeastOnce())
            ->method('findAuthCodeByToken')
            ->with('123_abc')
            ->willReturn(null);

        $this->authCodeManager->expects($this->never())
            ->method('deleteAuthCode');

        $this->storage->markAuthCodeAsUsed('123_abc');
    }
}

class User implements UserInterface
{
    private $username;

    public function __construct($username)
    {
        $this->username = $username;
    }

    public function getRoles()
    {
    }

    public function getPassword()
    {
    }

    public function getSalt()
    {
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function eraseCredentials()
    {
    }
}
