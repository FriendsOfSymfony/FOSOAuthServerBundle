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

use FOS\OAuthServerBundle\Command\CreateClientCommand;
use FOS\OAuthServerBundle\Tests\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\Container;

class CreateClientCommandTest extends TestCase
{
    /**
     * @var CreateClientCommand
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
        $command = new CreateClientCommand();

        $application = new Application();
        $application->add($command);

        $this->container = new Container();

        /** @var CreateClientCommand $command */
        $command = $application->find($command->getName());
        $command->setContainer($this->container);

        $this->command = $command;
    }

    /**
     * @dataProvider classProvider
     *
     * @param mixed $clientManager
     * @param mixed $client
     */
    public function testItShouldCreateClient($clientManager, $client)
    {
        $clientManager = $this
            ->getMockBuilder($clientManager)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $clientManager
            ->expects($this->any())
            ->method('createClient')
            ->will($this->returnValue(new $client()))
        ;

        $this->container->set('fos_oauth_server.client_manager.default', $clientManager);

        $commandTester = new CommandTester($this->command);

        $commandTester->execute([
            'command' => $this->command->getName(),
            '--redirect-uri' => ['https://www.example.com/oauth2/callback'],
            '--grant-type' => [
                'authorization_code',
                'password',
                'refresh_token',
                'token',
                'client_credentials',
            ],
        ]);

        $this->assertSame(0, $commandTester->getStatusCode());

        $output = $commandTester->getDisplay();

        $this->assertContains('Client ID', $output);
        $this->assertContains('Client Secret', $output);
    }

    /**
     * @return array
     */
    public function classProvider()
    {
        return [
            ['FOS\OAuthServerBundle\Document\ClientManager', 'FOS\OAuthServerBundle\Document\Client'],
            ['FOS\OAuthServerBundle\Entity\ClientManager', 'FOS\OAuthServerBundle\Entity\Client'],
            ['FOS\OAuthServerBundle\Model\ClientManager', 'FOS\OAuthServerBundle\Model\Client'],
            ['FOS\OAuthServerBundle\Propel\ClientManager', 'FOS\OAuthServerBundle\Propel\Client'],
        ];
    }
}
