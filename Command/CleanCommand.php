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

namespace FOS\OAuthServerBundle\Command;

use FOS\OAuthServerBundle\Model\AuthCodeManagerInterface;
use FOS\OAuthServerBundle\Model\TokenManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CleanCommand extends Command
{
    private $accessTokenManager;
    private $refreshTokenManager;
    private $authCodeManager;

    public function __construct(
        TokenManagerInterface $accessTokenManager,
        TokenManagerInterface $refreshTokenManager,
        AuthCodeManagerInterface $authCodeManager)
    {
        parent::__construct();

        $this->accessTokenManager = $accessTokenManager;
        $this->refreshTokenManager = $refreshTokenManager;
        $this->authCodeManager = $authCodeManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('fos:oauth-server:clean')
            ->setDescription('Clean expired tokens')
            ->setHelp(<<<EOT
The <info>%command.name%</info> command will remove expired OAuth2 tokens.

  <info>php %command.full_name%</info>
EOT
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach ([$this->accessTokenManager, $this->refreshTokenManager, $this->authCodeManager] as $service) {
            $result = $service->deleteExpired();
            $output->writeln(sprintf('Removed <info>%d</info> items from <comment>%s</comment> storage.', $result, get_class($service)));
        }
    }
}
