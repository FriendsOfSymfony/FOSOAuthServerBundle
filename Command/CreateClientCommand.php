<?php
/**
 * Created by PhpStorm.
 * User: toby
 * Date: 14/06/15
 * Time: 00:03
 */

namespace FOS\OAuthServerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Routing\Router;

class CreateClientCommand extends ContainerAwareCommand
{

    const OPTION_VAR_REDIRECT = 'redirect';

    const OPTION_VAR_GRANT_TYPE = 'grant-type';

    const OPTION_QUESTION_REDIRECT = 'What possible redirect URLs should be supported?';

    const OPTION_QUESTION_GRANT_TYPE = 'What grant types should the client support?';


    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('fos:oauth-server:client:create')
            ->setDescription('Creates a new client')
            ->setHelp(
                <<<EOT
                The <info>%command.name%</info> will create a new client.

  <info>php %command.full_name%</info>
EOT
            )
            ->addOption(
                self::OPTION_VAR_REDIRECT,
                'r',
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                self::OPTION_QUESTION_REDIRECT,
                []
            )
            ->addOption(
                self::OPTION_VAR_GRANT_TYPE,
                'g',
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                self::OPTION_QUESTION_GRANT_TYPE,
                []
            );
    }


    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $redirects  = $this->getRedirects($input, $output);
        $grantTypes = $this->getGrantTypes($input, $output);

        $clientManager = $this->getClientManager();
        $client        = $clientManager->createClient();

        $client->setRedirectUris($redirects);
        $client->setAllowedGrantTypes($grantTypes);
        $clientManager->updateClient($client);

        $output->writeln('<info>Client created with…</info>');
        $output->writeln('<info>Redirects:</info> ' . implode(', ', $redirects));
        $output->writeln('<info>Grant Types:</info> ' . implode(', ', $grantTypes));

        $output->writeln('');
        $output->writeln("<info>Client created…</info>");
        $output->writeln('Client ID: ' . $client->getPublicId());
        $output->writeln('Client secret: ' . $client->getSecret());
        $output->writeln('');
    }


    /**
     * @return \FOS\OAuthServerBundle\Entity\ClientManager
     */
    protected function getClientManager()
    {
        return $this->getContainer()
                    ->get('fos_oauth_server.client_manager.default');
    }


    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return array
     */
    protected function getRedirects(InputInterface $input, OutputInterface $output)
    {
        $redirects = $input->getOption(self::OPTION_VAR_REDIRECT);
        if (empty($redirects)) {
            $question = $this->prepareQuestion(self::OPTION_QUESTION_REDIRECT);
            do {
                $redirects = $this->askTillNull($question, $input, $output);
                if (empty($redirects)) {
                    $this->askForAtLeastOneAnswer($output);
                }
            } while (empty($redirects));
        }

        return $redirects;
    }


    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return array
     */
    protected function getGrantTypes(InputInterface $input, OutputInterface $output)
    {
        $grantTypes = $input->getOption(self::OPTION_VAR_GRANT_TYPE);
        if (empty($grantTypes)) {
            $question = $this->prepareQuestion(self::OPTION_QUESTION_GRANT_TYPE);
            do {
                $grantTypes = $this->askTillNull($question, $input, $output);
                if (empty($grantTypes)) {
                    $this->askForAtLeastOneAnswer($output);
                }
            } while (empty($grantTypes));
        }

        return $grantTypes;
    }


    /**
     * @return mixed
     */
    protected function getQuestionHelper()
    {
        return $this->getHelper('question');
    }


    /**
     * Asks the same question over & over until not answered
     *
     * @param Question        $question Question text
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return array
     */
    private function askTillNull(Question $question, InputInterface $input, OutputInterface $output)
    {
        $answers        = [];
        $questionHelper = $this->getQuestionHelper();
        do {
            $answer    = $questionHelper->ask($input, $output, $question);
            $answers[] = $answer;
        } while (!empty($answer));

        return array_filter($answers);
    }


    /**
     * @param $output
     */
    private function askForAtLeastOneAnswer(OutputInterface $output)
    {
        $output->writeln('<error>Please provide at lease one value</error>');
    }


    /**
     * @param $question
     *
     * @return Question
     */
    protected function prepareQuestion($question)
    {
        return new Question("<info>$question</info>", null);
    }


    /**
     * @return \Symfony\Bundle\FrameworkBundle\Routing\Router
     */
    protected function getRouter()
    {
        return $this->getContainer()->get('router');
    }
}