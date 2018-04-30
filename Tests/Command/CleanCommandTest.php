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
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class CleanCommandTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CleanCommand
     */
    private $command;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|TokenManagerInterface
     */
    private $accessTokenManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|TokenManagerInterface
     */
    private $refreshTokenManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|AuthCodeManagerInterface
     */
    private $authCodeManager;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
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
    public function testItShouldRemoveExpiredToken()
    {
        $expiredAccessTokens = 5;
        $this->accessTokenManager
            ->expects($this->once())
            ->method('deleteExpired')
            ->will($this->returnValue($expiredAccessTokens))
        ;

        $expiredRefreshTokens = 183;
        $this->refreshTokenManager
            ->expects($this->once())
            ->method('deleteExpired')
            ->will($this->returnValue($expiredRefreshTokens))
        ;

        $expiredAuthCodes = 0;
        $this->authCodeManager
            ->expects($this->once())
            ->method('deleteExpired')
            ->will($this->returnValue($expiredAuthCodes))
        ;

        $tester = new CommandTester($this->command);
        $tester->execute(['command' => $this->command->getName()]);

        $display = $tester->getDisplay();

        $this->assertContains(sprintf('Removed %d items from %s storage.', $expiredAccessTokens, get_class($this->accessTokenManager)), $display);
        $this->assertContains(sprintf('Removed %d items from %s storage.', $expiredRefreshTokens, get_class($this->refreshTokenManager)), $display);
        $this->assertContains(sprintf('Removed %d items from %s storage.', $expiredAuthCodes, get_class($this->authCodeManager)), $display);
    }

    /**
     * Skip classes for deleting expired tokens that do not implement AuthCodeManagerInterface or TokenManagerInterface.
     */
    public function testItShouldNotRemoveExpiredTokensForOtherClasses()
    {
        $this->markTestIncomplete('Needs a better way of testing this');

        $tester = new CommandTester($this->command);
        $tester->execute(['command' => $this->command->getName()]);

        $display = $tester->getDisplay();

        $this->assertNotRegExp(sprintf('\'Removed (\d)+ items from %s storage.\'', get_class($this->accessTokenManager)), $display);
        $this->assertNotRegExp(sprintf('\'Removed (\d)+ items from %s storage.\'', get_class($this->refreshTokenManager)), $display);
        $this->assertNotRegExp(sprintf('\'Removed (\d)+ items from %s storage.\'', get_class($this->authCodeManager)), $display);
    }
}
