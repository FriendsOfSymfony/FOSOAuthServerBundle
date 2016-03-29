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
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class CleanCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \FOS\OAuthServerBundle\Command\CleanCommand
     */
    private $command;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $command = new CleanCommand();

        $application = new Application();
        $application->add($command);

        $this->container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');

        $this->command = $application->find($command->getName());
        $this->command->setContainer($this->container);
    }

    /**
     * Delete expired tokens for classes that implement the TokenManagerInterface.
     */
    public function testItShouldRemoveExpiredTokensForTokenManagerInterfaces()
    {
        $expiredAccessTokens = 5;
        $accessTokenManager = $this->getMock('FOS\OAuthServerBundle\Model\TokenManagerInterface');
        $accessTokenManager
            ->expects($this->once())
            ->method('deleteExpired')
            ->will($this->returnValue($expiredAccessTokens));

        $expiredRefreshTokens = 3;
        $refreshTokenManager = $this->getMock('FOS\OAuthServerBundle\Model\TokenManagerInterface');
        $refreshTokenManager
            ->expects($this->once())
            ->method('deleteExpired')
            ->will($this->returnValue($expiredRefreshTokens));

        $expiredAuthCodes = 0;
        $authCodeManager = $this->getMock('FOS\OAuthServerBundle\Model\TokenManagerInterface');
        $authCodeManager
            ->expects($this->once())
            ->method('deleteExpired')
            ->will($this->returnValue($expiredAuthCodes));

        $containerMap = array(
            'fos_oauth_server.access_token_manager' => $accessTokenManager,
            'fos_oauth_server.refresh_token_manager' => $refreshTokenManager,
            'fos_oauth_server.auth_code_manager' => $authCodeManager,
        );

        $this->container
            ->expects($this->exactly(count($containerMap)))
            ->method('get')
            ->will($this->returnCallback(function ($argument) use ($containerMap) {
                return $containerMap[$argument];
            }));

        $tester = new CommandTester($this->command);
        $tester->execute(array('command' => $this->command->getName()));
        $display = $tester->getDisplay();

        $this->assertRegExp(sprintf('\'Removed %d items from %s storage.\'', $expiredAccessTokens, 'Access token'), $display);
        $this->assertRegExp(sprintf('\'Removed %d items from %s storage.\'', $expiredRefreshTokens, 'Refresh token'), $display);
        $this->assertRegExp(sprintf('\'Removed %d items from %s storage.\'', $expiredAuthCodes, 'Auth code'), $display);
    }

    /**
     * Delete expired tokens for classes that implement the AuthCodeManagerInterface.
     */
    public function testItShouldRemoveExpiredTokensForAuthCodeManagerInterfaces()
    {
        $expiredAccessTokens = 5;
        $accessTokenManager = $this->getMock('FOS\OAuthServerBundle\Model\AuthCodeManagerInterface');
        $accessTokenManager
            ->expects($this->once())
            ->method('deleteExpired')
            ->will($this->returnValue($expiredAccessTokens));

        $expiredRefreshTokens = 3;
        $refreshTokenManager = $this->getMock('FOS\OAuthServerBundle\Model\AuthCodeManagerInterface');
        $refreshTokenManager
            ->expects($this->once())
            ->method('deleteExpired')
            ->will($this->returnValue($expiredRefreshTokens));

        $expiredAuthCodes = 0;
        $authCodeManager = $this->getMock('FOS\OAuthServerBundle\Model\AuthCodeManagerInterface');
        $authCodeManager
            ->expects($this->once())
            ->method('deleteExpired')
            ->will($this->returnValue($expiredAuthCodes));

        $containerMap = array(
            'fos_oauth_server.access_token_manager' => $accessTokenManager,
            'fos_oauth_server.refresh_token_manager' => $refreshTokenManager,
            'fos_oauth_server.auth_code_manager' => $authCodeManager,
        );

        $this->container
            ->expects($this->exactly(count($containerMap)))
            ->method('get')
            ->will($this->returnCallback(function ($argument) use ($containerMap) {
                return $containerMap[$argument];
            }));

        $tester = new CommandTester($this->command);
        $tester->execute(array('command' => $this->command->getName()));
        $display = $tester->getDisplay();

        $this->assertRegExp(sprintf('\'Removed %d items from %s storage.\'', $expiredAccessTokens, 'Access token'), $display);
        $this->assertRegExp(sprintf('\'Removed %d items from %s storage.\'', $expiredRefreshTokens, 'Refresh token'), $display);
        $this->assertRegExp(sprintf('\'Removed %d items from %s storage.\'', $expiredAuthCodes, 'Auth code'), $display);
    }

    /**
     * Skip classes for deleting expired tokens that do not implement AuthCodeManagerInterface or TokenManagerInterface.
     */
    public function testItShouldNotRemoveExpiredTokensForOtherClasses()
    {
        $containerMap = array(
            'fos_oauth_server.access_token_manager' => new \stdClass(),
            'fos_oauth_server.refresh_token_manager' => new \stdClass(),
            'fos_oauth_server.auth_code_manager' => new \stdClass(),
        );

        $this->container
            ->expects($this->exactly(count($containerMap)))
            ->method('get')
            ->will($this->returnCallback(function ($argument) use ($containerMap) {
                return $containerMap[$argument];
            }));

        $tester = new CommandTester($this->command);
        $tester->execute(array('command' => $this->command->getName()));
        $display = $tester->getDisplay();

        $this->assertNotRegExp(sprintf('\'Removed (\d)+ items from %s storage.\'', 'Access token'), $display);
        $this->assertNotRegExp(sprintf('\'Removed (\d)+ items from %s storage.\'', 'Refresh token'), $display);
        $this->assertNotRegExp(sprintf('\'Removed (\d)+ items from %s storage.\'', 'Auth code'), $display);
    }
}
