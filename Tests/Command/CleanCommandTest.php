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

namespace FOS\OAuthServerBundle\Tests\Command;

use FOS\OAuthServerBundle\Command\CleanCommand;
use FOS\OAuthServerBundle\Model\AuthCodeManagerInterface;
use FOS\OAuthServerBundle\Model\TokenManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class CleanCommandTest extends TestCase
{
    /**
     * @var CleanCommand
     */
    private $command;

    /**
     * @var MockObject|TokenManagerInterface
     */
    private $accessTokenManager;

    /**
     * @var MockObject|TokenManagerInterface
     */
    private $refreshTokenManager;

    /**
     * @var MockObject|AuthCodeManagerInterface
     */
    private $authCodeManager;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->accessTokenManager = $this->getMockBuilder(TokenManagerInterface::class)->disableOriginalConstructor()->getMock();
        $this->refreshTokenManager = $this->getMockBuilder(TokenManagerInterface::class)->disableOriginalConstructor()->getMock();
        $this->authCodeManager = $this->getMockBuilder(AuthCodeManagerInterface::class)->disableOriginalConstructor()->getMock();

        $command = new CleanCommand($this->accessTokenManager, $this->refreshTokenManager, $this->authCodeManager);

        $application = new Application();
        $application->add($command);

        /** @var CleanCommand $command */
        $command = $application->find($command->getName());

        $this->command = $command;
    }

    /**
     * Delete expired tokens for provided classes.
     */
    public function testItShouldRemoveExpiredToken(): void
    {
        $expiredAccessTokens = 5;
        $this->accessTokenManager
            ->expects($this->once())
            ->method('deleteExpired')
            ->willReturn($expiredAccessTokens)
        ;

        $expiredRefreshTokens = 183;
        $this->refreshTokenManager
            ->expects($this->once())
            ->method('deleteExpired')
            ->willReturn($expiredRefreshTokens)
        ;

        $expiredAuthCodes = 0;
        $this->authCodeManager
            ->expects($this->once())
            ->method('deleteExpired')
            ->willReturn($expiredAuthCodes)
        ;

        $tester = new CommandTester($this->command);
        $tester->execute(['command' => $this->command->getName()]);

        $display = $tester->getDisplay();

        self::assertStringContainsString(sprintf('Removed %d items from %s storage.', $expiredAccessTokens, get_class($this->accessTokenManager)), $display);
        self::assertStringContainsString(sprintf('Removed %d items from %s storage.', $expiredRefreshTokens, get_class($this->refreshTokenManager)), $display);
        self::assertStringContainsString(sprintf('Removed %d items from %s storage.', $expiredAuthCodes, get_class($this->authCodeManager)), $display);
    }

    /**
     * Skip classes for deleting expired tokens that do not implement AuthCodeManagerInterface or TokenManagerInterface.
     */
    public function testItShouldNotRemoveExpiredTokensForOtherClasses(): void
    {
        $this->markTestIncomplete('Needs a better way of testing this');

        $tester = new CommandTester($this->command);
        $tester->execute(['command' => $this->command->getName()]);

        $display = $tester->getDisplay();

        self::assertNotRegExp(sprintf('\'Removed (\d)+ items from %s storage.\'', get_class($this->accessTokenManager)), $display);
        self::assertNotRegExp(sprintf('\'Removed (\d)+ items from %s storage.\'', get_class($this->refreshTokenManager)), $display);
        self::assertNotRegExp(sprintf('\'Removed (\d)+ items from %s storage.\'', get_class($this->authCodeManager)), $display);
    }
}
