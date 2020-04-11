<?php

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Console\Application;

const HPDB_CRAWLER_NAME = 'hpdb-bot-m/0.1 (+https://hpdb.ru/crawler)';

$container = require __DIR__ . '/config/container.php';

$commands = [];
$command = new \App\Cli\Commands\MigrateSqliteToMongo();
$command->setMongo($container->get(CONTAINER_CONFIG_MONGO));
$command->setSqlite($container->get(SQLite3::class));
$commands[] = $command;
$command = new \App\Cli\Commands\ReindexHompages();
$command->setMongo($container->get(CONTAINER_CONFIG_MONGO));
$command->setCrawlersBus($container->get(CONTAINER_CONFIG_REDIS_STREAM_CRAWLERS));
$command->setWebsiteIndexer(
    new \App\Common\Services\Website\WebsiteIndexer(
        new \App\Common\Services\Website\WebsiteFetcher(new \App\Common\Services\HttpClient(HPDB_CRAWLER_NAME))
    )
);
$commands[] = $command;
$command = new \App\Cli\Commands\ExtractIndexedContent();
$command->setMongo($container->get(CONTAINER_CONFIG_MONGO));
$command->setProcessorBus($container->get(CONTAINER_CONFIG_REDIS_STREAM_PROCESSORS));
$commands[] = $command;
$command = new \App\Cli\Commands\AddWebsite();
$command->setMongo($container->get(CONTAINER_CONFIG_MONGO));
$command->setPersistorsBus($container->get(CONTAINER_CONFIG_REDIS_STREAM_PERSISTORS));
$command->setCrawlersBus($container->get(CONTAINER_CONFIG_REDIS_STREAM_CRAWLERS));
$commands[] = $command;
$command = new \App\Cli\Commands\GithubUserParser();
$command->setMongo($container->get(CONTAINER_CONFIG_MONGO));
$command->setPersistorsBus($container->get(CONTAINER_CONFIG_REDIS_STREAM_PERSISTORS));
$commands[] = $command;
$command = new \App\Cli\Commands\MoveWebsiteContentDataToWebsite();
$command->setMongo($container->get(CONTAINER_CONFIG_MONGO));
/* remove reindexer */
$commands[] = $command;
$command = new \App\Cli\Commands\NormalizeWebsites();
$command->setMongo($container->get(CONTAINER_CONFIG_MONGO));
$commands[] = $command;


$application = new Application();
$application->addCommands($commands);

$application->run();
