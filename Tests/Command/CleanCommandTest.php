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
use Symfony\Component\DependencyInjection\Container;

class CleanCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CleanCommand
     */
    private $command;

    /**
     * @var Container
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

        $this->container = new Container();

        $this->command = $application->find($command->getName());
        $this->command->setContainer($this->container);
    }

    /**
     * Delete expired tokens for provided classes.
     *
     * @dataProvider classProvider
     *
     * @param string $class A fully qualified class name.
     */
    public function testItShouldRemoveExpiredToken($class)
    {
        $expiredAccessTokens = 5;
        $accessTokenManager = $this->getMock($class);
        $accessTokenManager
            ->expects($this->once())
            ->method('deleteExpired')
            ->will($this->returnValue($expiredAccessTokens));

        $expiredRefreshTokens = 183;
        $refreshTokenManager = $this->getMock($class);
        $refreshTokenManager
            ->expects($this->once())
            ->method('deleteExpired')
            ->will($this->returnValue($expiredRefreshTokens));

        $expiredAuthCodes = 0;
        $authCodeManager = $this->getMock($class);
        $authCodeManager
            ->expects($this->once())
            ->method('deleteExpired')
            ->will($this->returnValue($expiredAuthCodes));

        $this->container->set('fos_oauth_server.access_token_manager', $accessTokenManager);
        $this->container->set('fos_oauth_server.refresh_token_manager', $refreshTokenManager);
        $this->container->set('fos_oauth_server.auth_code_manager', $authCodeManager);

        $tester = new CommandTester($this->command);
        $tester->execute(array('command' => $this->command->getName()));

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
        $tester->execute(array('command' => $this->command->getName()));

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
        return array(
            array('FOS\OAuthServerBundle\Model\TokenManagerInterface'),
            array('FOS\OAuthServerBundle\Model\AuthCodeManagerInterface'),
        );
    }
}
