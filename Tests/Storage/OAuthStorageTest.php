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

namespace FOS\OAuthServerBundle\Tests\Storage;

use FOS\OAuthServerBundle\Model\AccessToken;
use FOS\OAuthServerBundle\Model\AccessTokenManagerInterface;
use FOS\OAuthServerBundle\Model\AuthCode;
use FOS\OAuthServerBundle\Model\AuthCodeManagerInterface;
use FOS\OAuthServerBundle\Model\Client;
use FOS\OAuthServerBundle\Model\ClientManagerInterface;
use FOS\OAuthServerBundle\Model\RefreshToken;
use FOS\OAuthServerBundle\Model\RefreshTokenManagerInterface;
use FOS\OAuthServerBundle\Storage\OAuthStorage;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class OAuthStorageTest extends \PHPUnit\Framework\TestCase
{
    protected $clientManager;

    protected $accessTokenManager;

    protected $refreshTokenManager;

    protected $authCodeManager;

    protected $userProvider;

    protected $encoderFactory;

    protected $storage;

    public function setUp()
    {
        $this->clientManager = $this->getMockBuilder(ClientManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->accessTokenManager = $this->getMockBuilder(AccessTokenManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->refreshTokenManager = $this->getMockBuilder(RefreshTokenManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->authCodeManager = $this->getMockBuilder(AuthCodeManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->userProvider = $this->getMockBuilder(UserProviderInterface::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->encoderFactory = $this->getMockBuilder(EncoderFactoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->storage = new OAuthStorage($this->clientManager, $this->accessTokenManager, $this->refreshTokenManager, $this->authCodeManager, $this->userProvider, $this->encoderFactory);
    }

    public function testGetClientReturnsClientWithGivenId()
    {
        $client = new Client();

        $this->clientManager->expects($this->once())
            ->method('findClientByPublicId')
            ->with('123_abc')
            ->will($this->returnValue($client))
        ;

        $this->assertSame($client, $this->storage->getClient('123_abc'));
    }

    public function testGetClientReturnsNullIfNotExists()
    {
        $client = new Client();

        $this->clientManager->expects($this->once())
            ->method('findClientByPublicId')
            ->with('123_abc')
            ->will($this->returnValue(null))
        ;

        $this->assertNull($this->storage->getClient('123_abc'));
    }

    public function testCheckClientCredentialsThrowsIfInvalidClientClass()
    {
        $client = $this->getMockBuilder('OAuth2\Model\IOAuth2Client')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->expectException('InvalidArgumentException');
        $this->storage->checkClientCredentials($client, 'dummy');
    }

    public function testCheckClientCredentialsReturnsTrueOnValidCredentials()
    {
        $client = new Client();
        $client->setSecret('dummy');

        $this->assertTrue($this->storage->checkClientCredentials($client, 'dummy'));
    }

    public function testCheckClientCredentialsReturnsFalseOnValidCredentials()
    {
        $client = new Client();
        $client->setSecret('dummy');

        $this->assertFalse($this->storage->checkClientCredentials($client, 'passe'));
    }

    public function testGetAccessTokenReturnsAccessTokenWithGivenId()
    {
        $token = new AccessToken();

        $this->accessTokenManager->expects($this->once())
            ->method('findTokenByToken')
            ->with('123_abc')
            ->will($this->returnValue($token))
        ;

        $this->assertSame($token, $this->storage->getAccessToken('123_abc'));
    }

    public function testGetAccessTokenReturnsNullIfNotExists()
    {
        $token = new AccessToken();

        $this->accessTokenManager->expects($this->once())
            ->method('findTokenByToken')
            ->with('123_abc')
            ->will($this->returnValue(null))
        ;

        $this->assertNull($this->storage->getAccessToken('123_abc'));
    }

    public function testCreateAccessTokenThrowsOnInvalidClientClass()
    {
        $client = $this->getMockBuilder('OAuth2\Model\IOAuth2Client')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->expectException('InvalidArgumentException');
        $this->storage->createAccessToken('foo', $client, new User(42), 1, 'foo bar');
    }

    public function testCreateAccessToken()
    {
        $savedToken = null;

        $this->accessTokenManager->expects($this->once())
            ->method('createToken')
            ->with()
            ->will($this->returnValue(new AccessToken()))
        ;
        $this->accessTokenManager->expects($this->once())
            ->method('updateToken')
            ->will($this->returnCallback(function ($token) use (&$savedToken) {
                $savedToken = $token;
            }))
        ;

        $client = new Client();
        $user = new User(42);

        $token = $this->storage->createAccessToken('foo', $client, $user, 1, 'foo bar');

        $this->assertSame($token, $savedToken);

        $this->assertSame('foo', $token->getToken());
        $this->assertSame($client, $token->getClient());
        $this->assertSame($user, $token->getData());
        $this->assertSame($user, $token->getUser());
        $this->assertSame(1, $token->getExpiresAt());
        $this->assertSame('foo bar', $token->getScope());
    }

    public function testCreateAccessTokenWithoutUser()
    {
        $savedToken = null;

        $this->accessTokenManager->expects($this->once())
            ->method('createToken')
            ->with()
            ->will($this->returnValue(new AccessToken()))
        ;
        $this->accessTokenManager->expects($this->once())
            ->method('updateToken')
            ->will($this->returnCallback(function ($token) use (&$savedToken) {
                $savedToken = $token;
            }))
        ;

        $client = new Client();
        $user = null;

        $token = $this->storage->createAccessToken('foo', $client, $user, 1, 'foo bar');

        $this->assertSame($token, $savedToken);
    }

    public function testGetRefreshTokenReturnsRefreshTokenWithGivenId()
    {
        $token = new RefreshToken();

        $this->refreshTokenManager->expects($this->once())
            ->method('findTokenByToken')
            ->with('123_abc')
            ->will($this->returnValue($token))
        ;

        $this->assertSame($token, $this->storage->getRefreshToken('123_abc'));
    }

    public function testGetRefreshTokenReturnsNullIfNotExists()
    {
        $this->refreshTokenManager->expects($this->once())
            ->method('findTokenByToken')
            ->with('123_abc')
            ->will($this->returnValue(null))
        ;

        $this->assertNull($this->storage->getRefreshToken('123_abc'));
    }

    public function testCreateRefreshTokenThrowsOnInvalidClientClass()
    {
        $client = $this->getMockBuilder('OAuth2\Model\IOAuth2Client')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->expectException('InvalidArgumentException');
        $this->storage->createRefreshToken('foo', $client, 42, 1, 'foo bar');
    }

    public function testCreateRefreshToken()
    {
        $savedToken = null;

        $this->refreshTokenManager->expects($this->once())
            ->method('createToken')
            ->with()
            ->will($this->returnValue(new RefreshToken()))
        ;
        $this->refreshTokenManager->expects($this->once())
            ->method('updateToken')
            ->will($this->returnCallback(function ($token) use (&$savedToken) {
                $savedToken = $token;
            }))
        ;

        $client = new Client();
        $user = new User(42);

        $token = $this->storage->createRefreshToken('foo', $client, $user, 1, 'foo bar');

        $this->assertSame($token, $savedToken);

        $this->assertSame('foo', $token->getToken());
        $this->assertSame($client, $token->getClient());
        $this->assertSame($user, $token->getData());
        $this->assertSame($user, $token->getUser());
        $this->assertSame(1, $token->getExpiresAt());
        $this->assertSame('foo bar', $token->getScope());
    }

    public function testCreateRefreshTokenWithoutUser()
    {
        $savedToken = null;

        $this->refreshTokenManager->expects($this->once())
            ->method('createToken')
            ->with()
            ->will($this->returnValue(new RefreshToken()))
        ;
        $this->refreshTokenManager->expects($this->once())
            ->method('updateToken')
            ->will($this->returnCallback(function ($token) use (&$savedToken) {
                $savedToken = $token;
            }))
        ;

        $client = new Client();
        $user = null;

        $token = $this->storage->createRefreshToken('foo', $client, $user, 1, 'foo bar');

        $this->assertSame($token, $savedToken);
    }

    public function testCheckRestrictedGrantTypeThrowsOnInvalidClientClass()
    {
        $client = $this->getMockBuilder('OAuth2\Model\IOAuth2Client')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->expectException('InvalidArgumentException');

        $this->storage->checkRestrictedGrantType($client, 'foo');
    }

    public function testCheckRestrictedGrantType()
    {
        $client = new Client();
        $client->setAllowedGrantTypes(['foo', 'bar']);

        $this->assertTrue($this->storage->checkRestrictedGrantType($client, 'foo'));
        $this->assertTrue($this->storage->checkRestrictedGrantType($client, 'bar'));
        $this->assertFalse($this->storage->checkRestrictedGrantType($client, 'baz'));
    }

    public function testCheckUserCredentialsThrowsOnInvalidClientClass()
    {
        $client = $this->getMockBuilder('OAuth2\Model\IOAuth2Client')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->expectException('InvalidArgumentException');

        $this->storage->checkUserCredentials($client, 'Joe', 'baz');
    }

    public function testCheckUserCredentialsCatchesAuthenticationExceptions()
    {
        $client = new Client();

        $this->userProvider
            ->expects(self::once())
            ->method('loadUserByUsername')
            ->with('Joe')
            ->willThrowException(new AuthenticationException('No such user'))
        ;

        $result = $this->storage->checkUserCredentials($client, 'Joe', 'baz');

        $this->assertFalse($result);
    }

    public function testCheckUserCredentialsReturnsTrueOnValidCredentials()
    {
        $client = new Client();
        $user = $this->getMockBuilder('Symfony\Component\Security\Core\User\UserInterface')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $user->expects($this->once())
            ->method('getPassword')->with()->will($this->returnValue('foo'));
        $user->expects($this->once())
            ->method('getSalt')->with()->will($this->returnValue('bar'));

        $encoder = $this->getMockBuilder('Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $encoder->expects($this->once())
            ->method('isPasswordValid')
            ->with('foo', 'baz', 'bar')
            ->will($this->returnValue(true))
        ;

        $this->userProvider->expects($this->once())
            ->method('loadUserByUsername')
            ->with('Joe')
            ->will($this->returnValue($user))
        ;

        $this->encoderFactory->expects($this->once())
            ->method('getEncoder')
            ->with($user)
            ->will($this->returnValue($encoder))
        ;

        $this->assertSame([
            'data' => $user,
        ], $this->storage->checkUserCredentials($client, 'Joe', 'baz'));
    }

    public function testCheckUserCredentialsReturnsFalseOnInvalidCredentials()
    {
        $client = new Client();
        $user = $this->getMockBuilder('Symfony\Component\Security\Core\User\UserInterface')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $user->expects($this->once())
            ->method('getPassword')->with()->will($this->returnValue('foo'));
        $user->expects($this->once())
            ->method('getSalt')->with()->will($this->returnValue('bar'));

        $encoder = $this->getMockBuilder('Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $encoder->expects($this->once())
            ->method('isPasswordValid')
            ->with('foo', 'baz', 'bar')
            ->will($this->returnValue(false))
        ;

        $this->userProvider->expects($this->once())
            ->method('loadUserByUsername')
            ->with('Joe')
            ->will($this->returnValue($user))
        ;

        $this->encoderFactory->expects($this->once())
            ->method('getEncoder')
            ->with($user)
            ->will($this->returnValue($encoder))
        ;

        $this->assertFalse($this->storage->checkUserCredentials($client, 'Joe', 'baz'));
    }

    public function testCheckUserCredentialsReturnsFalseIfUserNotExist()
    {
        $client = new Client();

        $this->userProvider->expects($this->once())
            ->method('loadUserByUsername')
            ->with('Joe')
            ->willThrowException(new AuthenticationException('No such user'))
        ;

        $this->assertFalse($this->storage->checkUserCredentials($client, 'Joe', 'baz'));
    }

    public function testCreateAuthCodeThrowsOnInvalidClientClass()
    {
        $client = $this->getMockBuilder('OAuth2\Model\IOAuth2Client')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->expectException('InvalidArgumentException');
        $this->storage->createAuthCode('foo', $client, 42, 'http://www.example.com/', 1, 'foo bar');
    }

    public function testCreateAuthCode()
    {
        $savedCode = null;

        $this->authCodeManager->expects($this->once())
            ->method('createAuthCode')
            ->with()
            ->will($this->returnValue(new AuthCode()))
        ;
        $this->authCodeManager->expects($this->once())
            ->method('updateAuthCode')
            ->will($this->returnCallback(function ($code) use (&$savedCode) {
                $savedCode = $code;
            }))
        ;

        $client = new Client();
        $user = new User(42);

        $code = $this->storage->createAuthCode('foo', $client, $user, 'http://www.example.com/', 1, 'foo bar');

        $this->assertSame($code, $savedCode);

        $this->assertSame('foo', $code->getToken());
        $this->assertSame($client, $code->getClient());
        $this->assertSame($user, $code->getData());
        $this->assertSame($user, $code->getUser());
        $this->assertSame(1, $code->getExpiresAt());
        $this->assertSame('foo bar', $code->getScope());
    }

    public function testGetAuthCodeReturnsAuthCodeWithGivenId()
    {
        $code = new AuthCode();

        $this->authCodeManager->expects($this->once())
            ->method('findAuthCodeByToken')
            ->with('123_abc')
            ->will($this->returnValue($code))
        ;

        $this->assertSame($code, $this->storage->getAuthCode('123_abc'));
    }

    public function testGetAuthCodeReturnsNullIfNotExists()
    {
        $this->authCodeManager->expects($this->once())
            ->method('findAuthCodeByToken')
            ->with('123_abc')
            ->will($this->returnValue(null))
        ;

        $this->assertNull($this->storage->getAuthCode('123_abc'));
    }

    public function testValidGrantExtension()
    {
        $grantExtension = $this->getMockBuilder('FOS\OAuthServerBundle\Storage\GrantExtensionInterface')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $grantExtension
            ->expects($this->once())
            ->method('checkGrantExtension')
            ->will($this->returnValue(true))
        ;
        $this->storage->setGrantExtension('https://friendsofsymfony.com/grants/foo', $grantExtension);

        $client = $this->getMockBuilder('OAuth2\Model\IOAuth2Client')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->assertTrue($this->storage->checkGrantExtension($client, 'https://friendsofsymfony.com/grants/foo', [], []));
    }

    public function testInvalidGrantExtension()
    {
        $this->expectException(\OAuth2\OAuth2ServerException::class);

        $client = $this->getMockBuilder('OAuth2\Model\IOAuth2Client')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->storage->checkGrantExtension($client, 'https://friendsofsymfony.com/grants/bar', [], []);
    }

    public function testDoubleSetGrantExtension()
    {
        $grantExtension = $this->getMockBuilder('FOS\OAuthServerBundle\Storage\GrantExtensionInterface')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $grantExtension2 = $this->getMockBuilder('FOS\OAuthServerBundle\Storage\GrantExtensionInterface')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->storage->setGrantExtension($uri = 'https://friendsofsymfony.com/grants/foo', $grantExtension);
        $this->storage->setGrantExtension($uri, $grantExtension2);

        $storageClass = new \ReflectionClass(get_class($this->storage));
        $grantExtensionsProperty = $storageClass->getProperty('grantExtensions');
        $grantExtensionsProperty->setAccessible(true);
        $grantExtensions = $grantExtensionsProperty->getValue($this->storage);

        $this->assertSame($grantExtension2, $grantExtensions[$uri]);
    }

    public function testMarkAuthCodeAsUsedIfAuthCodeFound()
    {
        $authCode = $this->getMockBuilder('FOS\OAuthServerBundle\Model\AuthCodeInterface')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->authCodeManager->expects($this->atLeastOnce())
            ->method('findAuthCodeByToken')
            ->with('123_abc')
            ->will($this->returnValue($authCode))
        ;

        $this->authCodeManager->expects($this->atLeastOnce())
            ->method('deleteAuthCode')
            ->with($authCode)
            ->will($this->returnValue(null))
        ;

        $this->storage->markAuthCodeAsUsed('123_abc');
    }

    public function testMarkAuthCodeAsUsedIfAuthCodeNotFound()
    {
        $this->authCodeManager->expects($this->atLeastOnce())
            ->method('findAuthCodeByToken')
            ->with('123_abc')
            ->will($this->returnValue(null))
        ;

        $this->authCodeManager->expects($this->never())
            ->method('deleteAuthCode')
        ;

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
