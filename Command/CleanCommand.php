<?php
/*
 * This file is part of the FOSOAuthServerBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\OAuthServerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use FOS\OAuthServerBundle\Model\TokenManagerInterface;
use FOS\OAuthServerBundle\Model\AuthCodeManagerInterface;

class CleanCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('fos:oauth-server:clean')
            ->setDescription('Clean expired tokens')
            ->setHelp(<<<EOT
The <info>%command.name%</info> command will remove expired OAuth2 tokens.

  <info>php %command.full_name%</info>
EOT
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $services = array(
            'fos_oauth_server.access_token_manager'     => 'Access token',
            'fos_oauth_server.refresh_token_manager'    => 'Refresh token',
            'fos_oauth_server.auth_code_manager'        => 'Auth code',
        );

        foreach ($services as $service => $name) {
            /** @var $instance TokenManagerInterface */
            $instance = $this->getContainer()->get($service);
            if ($instance instanceof TokenManagerInterface || $instance instanceof AuthCodeManagerInterface) {
                $result = $instance->deleteExpired();
                $output->writeln(sprintf('Removed <info>%d</info> items from <comment>%s</comment> storage.', $result, $name));
            }
        }
    }
}
