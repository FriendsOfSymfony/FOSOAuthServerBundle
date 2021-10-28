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

namespace FOS\OAuthServerBundle\Tests\Security\Authentication\Passport;

use FOS\OAuthServerBundle\Security\Authentication\Passport\OAuthCredentials;
use Symfony\Component\Security\Core\User\UserInterface;

class OAuthCredentialsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|UserInterface
     */
    protected $user;

    public function setUp(): void
    {
        $this->user = $this->getMockBuilder(UserInterface::class)->disableOriginalConstructor()->getMock();
    }

    /**
     * @testWith ["scope_1 scope_2", ["ROLE_USER"], ["ROLE_USER", "ROLE_SCOPE_1", "ROLE_SCOPE_2"], "roles and scopes combined"]
     *           ["scope_1 duplicate", ["ROLE_USER", "ROLE_DUPLICATE"], ["ROLE_USER", "ROLE_DUPLICATE", "ROLE_SCOPE_1"], "only unique roles"]
     *           ["", ["ROLE_USER"], ["ROLE_USER"], "scopes can be empty"]
     *           ["scope", [], ["ROLE_SCOPE"], "user roles can be empty"]
     *           ["", [], [], "roles and scopes can be empty"]
     */
    public function testGetRoles(string $scopes, array $userRoles, array $expectedRoles, string $testCase): void
    {
        $credentials = new OAuthCredentials('mock_token', $scopes);

        $this->user->expects($this->once())
            ->method('getRoles')
            ->will($this->returnValue($userRoles))
        ;

        $this->assertSame($expectedRoles, $credentials->getRoles($this->user));
    }

    public function testMarkResolved(): void
    {
        $credentials = new OAuthCredentials('mock_token', 'scope_1 scope_2');

        $this->user->expects($this->any())
            ->method('getRoles')
            ->will($this->returnValue([]))
        ;

        $this->assertFalse($credentials->isResolved());
        $this->assertSame('mock_token', $credentials->getTokenString());
        $this->assertSame(['ROLE_SCOPE_1', 'ROLE_SCOPE_2'], $credentials->getRoles($this->user));

        $credentials->markResolved();

        // marking credentials as resolved should not change any other state,
        // as the transported data is still needed for creating the
        // authenticated token when the AuthenticatorManager progresses in
        // executing the OAuthAuthenticator
        $this->assertTrue($credentials->isResolved());
        $this->assertSame('mock_token', $credentials->getTokenString());
        $this->assertSame(['ROLE_SCOPE_1', 'ROLE_SCOPE_2'], $credentials->getRoles($this->user));
    }
}
