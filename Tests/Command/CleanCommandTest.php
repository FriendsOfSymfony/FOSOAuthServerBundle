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

        $this->command = $application->find($command->getName());
    }

    /**
     * Delete expired tokens for provided classes.
     *
     * @dataProvider classProvider
     *
     * @param string $class a fully qualified class name
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

        $this->assertContains(sprintf('Removed %d items from %s storage.', $expiredAccessTokens, 'Access token'), $display);
        $this->assertContains(sprintf('Removed %d items from %s storage.', $expiredRefreshTokens, 'Refresh token'), $display);
        $this->assertContains(sprintf('Removed %d items from %s storage.', $expiredAuthCodes, 'Auth code'), $display);
    }

    /**
     * Skip classes for deleting expired tokens that do not implement AuthCodeManagerInterface or TokenManagerInterface.
     */
    public function testItShouldNotRemoveExpiredTokensForOtherClasses()
    {
        $this->container->set('fos_oauth_server.access_token_manager', new \stdClass());
        $this->container->set('fos_oauth_server.refresh_token_manager', new \stdClass());
        $this->container->set('fos_oauth_server.auth_code_manager', new \stdClass());

        $tester = new CommandTester($this->command);
        $tester->execute(['command' => $this->command->getName()]);

        $display = $tester->getDisplay();

        $this->assertNotRegExp(sprintf('\'Removed (\d)+ items from %s storage.\'', 'Access token'), $display);
        $this->assertNotRegExp(sprintf('\'Removed (\d)+ items from %s storage.\'', 'Refresh token'), $display);
        $this->assertNotRegExp(sprintf('\'Removed (\d)+ items from %s storage.\'', 'Auth code'), $display);
    }

    /**
     * Provides the classes that should be accepted by the CleanCommand.
     *
     * @return array[]
     */
    public function classProvider()
    {
        return [
            ['FOS\OAuthServerBundle\Model\TokenManagerInterface'],
            ['FOS\OAuthServerBundle\Model\AuthCodeManagerInterface'],
        ];
    }
}
