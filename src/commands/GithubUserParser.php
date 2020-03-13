<?php

namespace app\commands;

use app\messageBus\messages\persistors\NewGithubProfileToPersistMessage;
use app\messageBus\repositories\GithubProfileRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class GithubUserParser extends Command
{
    /** @var \Jenssegers\Mongodb\Connection */
    private $mongo;
    /** @var MessageBusInterface */
    private $persistorsBus;

    public function setMongo(\Jenssegers\Mongodb\Connection $mongo)
    {
        $this->mongo = $mongo;
    }

    public function setPersistorsBus(MessageBusInterface $persistorsBus)
    {
        $this->persistorsBus = $persistorsBus;
    }

    /** {@inheritdoc} */
    protected function configure()
    {
        $this
            ->setName('parser:github-users')
            ->setDescription('Find blogs in github profiles: explore all the friends of a user; --login')
            ->addOption('login', null, InputOption::VALUE_REQUIRED, 'login of a profile');
    }

    /** {@inheritdoc} */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $login = (string)$input->getOption('login');
        if (!\preg_match('/^[a-zA-Z0-9-]{1,39}$/', $login)) {
            $output->writeln('Username is invalid');
            exit(1);
        }
        $login = (string)$input->getOption('login');

        $repo = new GithubProfileRepository($this->mongo);
        if ($profile = $repo->getOneByLogin($login)) {
            $output->writeln('Profile already exists');

            return 1;
        }

        $message = new NewGithubProfileToPersistMessage($login, new \DateTime(), null);
        $this->persistorsBus->dispatch($message);

        $output->writeln("Queued {$login}");

        return 0;
    }
}
