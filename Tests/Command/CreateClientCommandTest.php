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

use FOS\OAuthServerBundle\Command\CreateClientCommand;
use FOS\OAuthServerBundle\Entity\Client;
use FOS\OAuthServerBundle\Model\ClientManagerInterface;
use FOS\OAuthServerBundle\Tests\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class CreateClientCommandTest extends TestCase
{
    /**
     * @var CreateClientCommand
     */
    private $command;

    /**
     * @var \\PHPUnit\Framework\MockObject\MockObject|ClientManagerInterface
     */
    private $clientManager;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->clientManager = $this->getMockBuilder(ClientManagerInterface::class)->disableOriginalConstructor()->getMock();
        $command = new CreateClientCommand($this->clientManager);

        $application = new Application();
        $application->add($command);

        $this->command = $application->find($command->getName());
    }

    /**
     * @dataProvider clientProvider
     */
    public function testItShouldCreateClient(string $client): void
    {
        $this->clientManager
            ->method('createClient')
            ->willReturn(new $client);

        $commandTester = new CommandTester($this->command);

        $commandTester->execute(
            [
                'command' => $this->command->getName(),
                '--redirect-uri' => ['https://www.example.com/oauth2/callback'],
                '--grant-type' => [
                    'authorization_code',
                    'password',
                    'refresh_token',
                    'token',
                    'client_credentials',
                ],
            ]
        );

        self::assertEquals(0, $commandTester->getStatusCode());

        $output = $commandTester->getDisplay();

        self::assertStringContainsString('Client ID', $output);
        self::assertStringContainsString('Client Secret', $output);
    }

    public function clientProvider(): array
    {
        return [
            [Client::class],
            [\FOS\OAuthServerBundle\Model\Client::class],
        ];
    }
}
