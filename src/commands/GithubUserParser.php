<?php
namespace app\commands;

use app\models\GithubProfile;
use Guzzle\Http\Client;
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
            ->setDescription('Find blogs in github profiles: explore all the friends of a user; --startWithUser')
            ->addOption('startWithUser', null, InputOption::VALUE_REQUIRED, 'Url of website')
        ;
    }

    /** @inheritDoc */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $login = (string)$input->getOption('startWithUser');
        if (!\preg_match('/^[a-zA-Z0-9-]{1,39}$/', $login)) {
            $output->writeln('Username is invalid');
            exit(1);
        }

        $client = new Client('https://api.github.com/');
        $response = $client->get('/users/' . $login)->send();
        if ($response->getStatusCode() !== 200) {
            $output->writeln('Server returned ' . $response->getStatusCode());
            return;
        }
        $data = \json_decode($response->getBody(true));
        /** @var GithubProfile $profile */
        $profile = GithubProfile::query()->where(['login' => $login])->first();
        if (!$profile) {
            $profile = new GithubProfile();
            $profile->fill((array)$data);
        } elseif ($data->blog) {
            $profile->blog = $data->blog;
        }
        $profile->save();

        $output->writeln((string)$profile->_id);
    }

}
