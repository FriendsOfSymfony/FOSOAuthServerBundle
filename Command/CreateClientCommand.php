<?php

namespace FOS\OAuthServerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Burak Seydioglu
 * @link http://blog.logicexception.com/2012/04/securing-syfmony2-rest-service-wiith.html
 */
class CreateClientCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('fos:oauth-server:create-client')
            ->setDescription('Creates a new client')
            ->addArgument('name', InputArgument::REQUIRED, 'Sets the client name', null)
            ->addOption('redirect-uri', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Sets redirect uri for client. Use this option multiple times to set multiple redirect URIs.')
            ->addOption('grant-type', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Sets allowed grant type for client. Use this option multiple times to set multiple grant types.')
            ->setHelp(<<<EOT
The <info>%command.name%</info>command creates a new client.

  <info>php %command.full_name% [--redirect-uri=...] [--grant-type=...] name</info>
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $clientManager = $this->getContainer()->get('fos_oauth_server.client_manager');
        $client = $clientManager->createClient();
        $client->setName($input->getArgument('name'));
        $client->setRedirectUris($input->getOption('redirect-uri'));
        $client->setAllowedGrantTypes($input->getOption('grant-type'));
        $clientManager->updateClient($client);
        $output->writeln('Added a new client.');
        $output->writeln(sprintf('Name: <info>%s</info>', $client->getName()));
        $output->writeln(sprintf('Public Id: <info>%s</info>', $client->getPublicId()));
        $output->writeln(sprintf('Secret: <info>%s</info>', $client->getSecret()));
    }
}
