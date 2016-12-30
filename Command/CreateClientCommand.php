<?php

namespace FOS\OAuthServerBundle\Command;

use FOS\OAuthServerBundle\Model\ClientManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CreateClientCommand extends Command
{
    const OPTION_REDIRECT_URI = 'redirect-uri';
    const OPTION_GRANT_TYPE = 'grant-type';

    /**
     * @var ClientManager
     */
    private $clientManager;

    public function __construct(ClientManager $clientManager)
    {
        parent::__construct();

        $this->clientManager = $clientManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('fos:oauth-server:create-client')
            ->setDescription('Create a new OAuth client')
            ->setHelp(<<<EOT
The <info>%command.name%</info> command will create a new OAuth2 client.

  <info>php %command.full_name%</info>
EOT
            )
            ->addOption(
                self::OPTION_REDIRECT_URI,
                null,
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'If set add a redirect uri'
            )
            ->addOption(
                self::OPTION_GRANT_TYPE,
                null,
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'If set add a grant type'
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $client = $this->clientManager->createClient();
        $client->setRedirectUris($input->getOption(self::OPTION_REDIRECT_URI));
        $client->setAllowedGrantTypes($input->getOption(self::OPTION_GRANT_TYPE));
        $this->clientManager->updateClient($client);

        $output->writeln('Client created');
        $output->writeln('client_id='.$client->getId().'_'.$client->getRandomId());
        $output->writeln('client_secret='.$client->getSecret());
    }
}
