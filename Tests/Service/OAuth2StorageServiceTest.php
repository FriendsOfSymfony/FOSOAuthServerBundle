<?php

namespace Alb\OAuth2ServiceBundle\Tests\Service;

use Alb\OAuth2ServerBundle\Model\OAuth2Client;
use Alb\OAuth2ServerBundle\Service\OAuth2StorageService;
use Alb\OAuth2ServerBundle\Model\OAuth2AccessToken;
use Alb\OAuth2ServerBundle\Model\OAuth2AuthCode;

class OAuth2StorageServiceTest extends \PHPUnit_Framework_TestCase
{
    protected $clientManager;
    protected $accessTokenManager;
    protected $authCodeManager;
    protected $userProvider;
    protected $encoderFactory;
    protected $storage;

    public function setUp()
    {
        $this->clientManager = $this->getMock('Alb\OAuth2ServerBundle\Model\OAuth2ClientManagerInterface');
        $this->accessTokenManager = $this->getMock('Alb\OAuth2ServerBundle\Model\OAuth2AccessTokenManagerInterface');
        $this->authCodeManager = $this->getMock('Alb\OAuth2ServerBundle\Model\OAuth2AuthCodeManagerInterface');
        $this->userProvider = $this->getMock('Symfony\Component\Security\Core\User\UserProviderInterface');
        $this->encoderFactory = $this->getMock('Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface');

        $this->storage = new OAuth2StorageService($this->clientManager, $this->accessTokenManager, $this->authCodeManager, $this->userProvider, $this->encoderFactory);
    }

    public function testGetClientReturnsClientWithGivenId()
    {
        $client = new OAuth2Client;

        $this->clientManager->expects($this->once())
            ->method('findClientByPublicId')
            ->with('123_abc')
            ->will($this->returnValue($client));

        $this->assertSame($client, $this->storage->getClient('123_abc'));
    }

    public function testGetClientReturnsNullIfNotExists()
    {
        $client = new OAuth2Client;

        $this->clientManager->expects($this->once())
            ->method('findClientByPublicId')
            ->with('123_abc')
            ->will($this->returnValue(null));

        $this->assertNull($this->storage->getClient('123_abc'));
    }

    public function testCheckClientCredentialsThrowsIfInvalidClientClass()
    {
        $client = $this->getMock('OAuth2\Model\IOAuth2Client');

        $this->setExpectedException('InvalidArgumentException');
        $this->storage->checkClientCredentials($client, 'dummy');
    }

    public function testCheckClientCredentialsReturnsTrueOnValidCredentials()
    {
        $client = new OAuth2Client;
        $client->setSecret('dummy');

        $this->assertTrue($this->storage->checkClientCredentials($client, 'dummy'));
    }

    public function testCheckClientCredentialsReturnsFalseOnValidCredentials()
    {
        $client = new OAuth2Client;
        $client->setSecret('dummy');

        $this->assertFalse($this->storage->checkClientCredentials($client, 'passe'));
    }

    public function testGetAccessTokenReturnsAccessTokenWithGivenId()
    {
        $token = new OAuth2AccessToken;

        $this->accessTokenManager->expects($this->once())
            ->method('findTokenByToken')
            ->with('123_abc')
            ->will($this->returnValue($token));

        $this->assertSame($token, $this->storage->getAccessToken('123_abc'));
    }

    public function testGetAccessTokenReturnsNullIfNotExists()
    {
        $token = new OAuth2AccessToken;

        $this->accessTokenManager->expects($this->once())
            ->method('findTokenByToken')
            ->with('123_abc')
            ->will($this->returnValue(null));

        $this->assertNull($this->storage->getAccessToken('123_abc'));
    }

    public function testCreateAccessTokenThrowsOnInvalidClientClass()
    {
        $client = $this->getMock('OAuth2\Model\IOAuth2Client');

        $this->setExpectedException('InvalidArgumentException');
        $this->storage->createAccessToken('foo', $client, 42, 1, 'foo bar');
    }

    public function testCreateAccessToken()
    {
        $savedToken = null;

        $this->accessTokenManager->expects($this->once())
            ->method('createToken')
            ->with()
            ->will($this->returnValue(new OAuth2AccessToken));
        $this->accessTokenManager->expects($this->once())
            ->method('updateToken')
            ->will($this->returnCallback(function($token) use (&$savedToken) {
                $savedToken = $token;
            }));

        $client = new OAuth2Client;

        $token = $this->storage->createAccessToken('foo', $client, 42, 1, 'foo bar');

        $this->assertEquals($token, $savedToken);

        $this->assertSame('foo', $token->getToken());
        $this->assertSame($client, $token->getClient());
        $this->assertSame(42, $token->getData());
        $this->assertSame(1, $token->getExpiresAt());
        $this->assertSame('foo bar', $token->getScope());
    }

    public function testCheckRestrictedGrantTypeThrowsOnInvalidClientClass()
    {
        $client = $this->getMock('OAuth2\Model\IOAuth2Client');

        $this->setExpectedException('InvalidArgumentException');

        $this->storage->checkRestrictedGrantType($client, 'foo');
    }

    public function testCheckRestrictedGrantType()
    {
        $client = new OAuth2Client;
        $client->setAllowedGrantTypes(array('foo', 'bar'));

        $this->assertTrue($this->storage->checkRestrictedGrantType($client, 'foo'));
        $this->assertTrue($this->storage->checkRestrictedGrantType($client, 'bar'));
        $this->assertFalse($this->storage->checkRestrictedGrantType($client, 'baz'));
    }

    public function testCheckUserCredentialsThrowsOnInvalidClientClass()
    {
        $client = $this->getMock('OAuth2\Model\IOAuth2Client');

        $this->setExpectedException('InvalidArgumentException');

        $this->storage->checkUserCredentials($client, 'Joe', 'baz');
    }

    public function testCheckUserCredentialsReturnsTrueOnValidCredentials()
    {
        $client = new OAuth2Client;
        $user = $this->getMock('Symfony\Component\Security\Core\User\UserInterface');
        $user->expects($this->once())
            ->method('getPassword')->with()->will($this->returnValue('foo'));
        $user->expects($this->once())
            ->method('getSalt')->with()->will($this->returnValue('bar'));

        $encoder = $this->getMock('Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface');
        $encoder->expects($this->once())
            ->method('isPasswordValid')
            ->with('foo', 'baz', 'bar')
            ->will($this->returnValue(true));

        $this->userProvider->expects($this->once())
            ->method('loadUserByUsername')
            ->with('Joe')
            ->will($this->returnValue($user));

        $this->encoderFactory->expects($this->once())
            ->method('getEncoder')
            ->with($user)
            ->will($this->returnValue($encoder));

        $this->assertSame(array(
            'data' => $user,
        ), $this->storage->checkUserCredentials($client, 'Joe', 'baz'));
    }

    public function testCheckUserCredentialsReturnsFalseOnInvalidCredentials()
    {
        $client = new OAuth2Client;
        $user = $this->getMock('Symfony\Component\Security\Core\User\UserInterface');
        $user->expects($this->once())
            ->method('getPassword')->with()->will($this->returnValue('foo'));
        $user->expects($this->once())
            ->method('getSalt')->with()->will($this->returnValue('bar'));

        $encoder = $this->getMock('Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface');
        $encoder->expects($this->once())
            ->method('isPasswordValid')
            ->with('foo', 'baz', 'bar')
            ->will($this->returnValue(false));

        $this->userProvider->expects($this->once())
            ->method('loadUserByUsername')
            ->with('Joe')
            ->will($this->returnValue($user));

        $this->encoderFactory->expects($this->once())
            ->method('getEncoder')
            ->with($user)
            ->will($this->returnValue($encoder));

        $this->assertFalse($this->storage->checkUserCredentials($client, 'Joe', 'baz'));
    }

    public function testCheckUserCredentialsReturnsFalseIfUserNotExist()
    {
        $client = new OAuth2Client;

        $this->userProvider->expects($this->once())
            ->method('loadUserByUsername')
            ->with('Joe')
            ->will($this->returnValue(null));

        $this->assertFalse($this->storage->checkUserCredentials($client, 'Joe', 'baz'));
    }

    public function testCreateAuthCodeThrowsOnInvalidClientClass()
    {
        $client = $this->getMock('OAuth2\Model\IOAuth2Client');

        $this->setExpectedException('InvalidArgumentException');
        $this->storage->createAuthCode('foo', $client, 42, 'http://www.example.com/', 1, 'foo bar');
    }

    public function testCreateAuthCode()
    {
        $savedCode = null;

        $this->authCodeManager->expects($this->once())
            ->method('createAuthCode')
            ->with()
            ->will($this->returnValue(new OAuth2AuthCode));
        $this->authCodeManager->expects($this->once())
            ->method('updateAuthCode')
            ->will($this->returnCallback(function($code) use (&$savedCode) {
                $savedCode = $code;
            }));

        $client = new OAuth2Client;

        $code = $this->storage->createAuthCode('foo', $client, 42, 'http://www.example.com/', 1, 'foo bar');

        $this->assertEquals($code, $savedCode);

        $this->assertSame('foo', $code->getToken());
        $this->assertSame($client, $code->getClient());
        $this->assertSame(42, $code->getData());
        $this->assertSame(1, $code->getExpiresAt());
        $this->assertSame('foo bar', $code->getScope());
    }

    public function testGetAuthCodeReturnsAuthCodeWithGivenId()
    {
        $code = new OAuth2AuthCode;

        $this->authCodeManager->expects($this->once())
            ->method('findAuthCodeByToken')
            ->with('123_abc')
            ->will($this->returnValue($code));

        $this->assertSame($code, $this->storage->getAuthCode('123_abc'));
    }

    public function testGetAuthCodeReturnsNullIfNotExists()
    {
        $code = new OAuth2AuthCode;

        $this->authCodeManager->expects($this->once())
            ->method('findAuthCodeByToken')
            ->with('123_abc')
            ->will($this->returnValue(null));

        $this->assertNull($this->storage->getAuthCode('123_abc'));
    }


}

