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
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use FOS\OAuthServerBundle\Model\TokenManagerInterface;
use FOS\OAuthServerBundle\Model\AuthCodeManagerInterface;

class CleanCommand extends ContainerAwareCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('fos:oauth:clean')
            ->setDescription('Clean expired tokens')
            ->setHelp(<<<EOT
The <info>fos:oauth:clean</info> command will remove expired oauth2 tokens

  <info>php app/console fos:oauth:clean</info>
EOT
            );
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $services = array(
            'fos_oauth_server.access.token.manager'     => 'Access token',
            'fos_oauth_server.refresh.token.manager'    => 'Refresh token',
            'fos_oauth_server.auth.code.manager'        => 'Auth code',
        );

        foreach ($services as $service => $name) {
            /** @var $instance TokenManagerInterface */
            $instance = $this->getContainer()->get($service);
            if ($instance instanceof TokenManagerInterface or $instance instanceof AuthCodeManagerInterface) {
                $result = $instance->deleteExpired();
                $output->writeln(sprintf('Removed %d items from %s storage.', $result, $name));
            }
        }
    }

}