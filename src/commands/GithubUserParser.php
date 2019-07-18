<?php
namespace app\commands;

use app\models\Website;
use app\models\WebsiteIndexHistory;
use Guzzle\Http\Url;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 *
 */
class GithubUserParser extends Command
{
    /** @var \Jenssegers\Mongodb\Connection */
    private $mongo;

    public function setMongo(\Jenssegers\Mongodb\Connection $mongo)
    {
        $this->mongo = $mongo;
    }

    /** @inheritDoc */
    protected function configure()
    {
        $this
            ->setName('parser:github-users')
            ->setDescription('Find blogs in github profiles: explore all the friends of a user')
            ->addOption('startWithUser', null, InputOption::VALUE_REQUIRED, 'Url of website')
        ;
    }

    /** @inheritDoc */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = (string)$input->getOption('startWithUser');
        if (!\preg_match('/^[a-zA-Z0-9]{1,39}&/', $username)) {
            $output->writeln('NOT PASS');
        } else {
            $output->writeln('PASS');
        }
    }

}
