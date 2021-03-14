<?php

/*
 * This file is part of the FOSOAuthServerBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\OAuthServerBundle\Tests\Command;

use FOS\OAuthServerBundle\Command\CleanCommand;
use FOS\OAuthServerBundle\Model\AuthCodeManagerInterface;
use FOS\OAuthServerBundle\Model\TokenManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class CleanCommandTest extends TestCase
{
    private CleanCommand $command;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|TokenManagerInterface
     */
    private $accessTokenManager;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|TokenManagerInterface
     */
    private $refreshTokenManager;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|AuthCodeManagerInterface
     */
    private $authCodeManager;

    protected function setUp(): void
    {
        $this->accessTokenManager = $this->getMockBuilder(TokenManagerInterface::class)->disableOriginalConstructor()->getMock();
        $this->refreshTokenManager = $this->getMockBuilder(TokenManagerInterface::class)->disableOriginalConstructor()->getMock();
        $this->authCodeManager = $this->getMockBuilder(AuthCodeManagerInterface::class)->disableOriginalConstructor()->getMock();

        $command = new CleanCommand($this->accessTokenManager, $this->refreshTokenManager, $this->authCodeManager);

        $application = new Application();
        $application->add($command);

        $this->command = $application->find($command->getName());
    }

    /**
     * Delete expired tokens for provided classes.
     */
    public function testItShouldRemoveExpiredToken(): void
    {
        $expiredAccessTokens = 5;
        $this->accessTokenManager
            ->expects(self::once())
            ->method('deleteExpired')
            ->willReturn($expiredAccessTokens);

        $expiredRefreshTokens = 183;
        $this->refreshTokenManager
            ->expects(self::once())
            ->method('deleteExpired')
            ->willReturn($expiredRefreshTokens);

        $expiredAuthCodes = 0;
        $this->authCodeManager
            ->expects(self::once())
            ->method('deleteExpired')
            ->willReturn($expiredAuthCodes);

        $tester = new CommandTester($this->command);
        $tester->execute(array('command' => $this->command->getName()));

        $display = $tester->getDisplay();

        $this->assertStringContainsString(sprintf('Removed %d items from %s storage.', $expiredAccessTokens, get_class($this->accessTokenManager)), $display);
        $this->assertStringContainsString(sprintf('Removed %d items from %s storage.', $expiredRefreshTokens, get_class($this->refreshTokenManager)), $display);
        $this->assertStringContainsString(sprintf('Removed %d items from %s storage.', $expiredAuthCodes, get_class($this->authCodeManager)), $display);
    }
}
