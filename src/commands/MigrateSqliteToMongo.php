<?php
namespace app\commands;

use app\models\Website;
use Illuminate\Database\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateSqliteToMongo extends Command
{
    private const PAGINATION_CNT = 200;

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
        $cnt = $this->sqlite->query()->from('all_profiles')->count() / self::PAGINATION_CNT + 1;
        for ($i = 0; $i < $cnt; $i++) {
            $profiles = $this->sqlite->query()
                ->select(['*'])->from('all_profiles')
                ->offset($i * self::PAGINATION_CNT)->limit(self::PAGINATION_CNT)
                ->get();
            foreach ($profiles as $profile) {
                Website::query()->insert((array)$profile);
            }
        }
        // Example code
        $output->writeLn("Data is moved.");

    }
}
