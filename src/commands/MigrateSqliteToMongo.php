<?php
namespace app\commands;

use Illuminate\Database\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateSqliteToMongo extends Command
{
    /** @var Connection */
    private $sqlite;
    /** @var \Jenssegers\Mongodb\Connection */
    private $mongo;

    public function setSqlite(Connection $sqlite)
    {
        $this->sqlite = $sqlite;
    }

    public function setMongo(\Jenssegers\Mongodb\Connection $mongo)
    {
        $this->mongo = $mongo;
    }

    /** @inheritDoc */
    protected function configure()
    {
        $this
            ->setName('app:migrate-sqlite-to-mongo')
            ->setDescription('Move data from SQLite to MongoDb.')
        ;
    }

    /** @inheritDoc */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $a = $this->sqlite->query()->select(['*'])->from('all_profiles')->limit(10)->get();
        var_dump($a);
        // Example code
        $output->writeLn("Data is moved.");

    }
}
