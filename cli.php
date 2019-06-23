<?php

require __DIR__.'/vendor/autoload.php';

use Symfony\Component\Console\Application;

const HPDB_CRAWLER_NAME = 'hpdb-bot/0.1';

$container = require __DIR__ . '/src/config/container.php';

$commands = [];
$command = new \app\commands\MigrateSqliteToMongo();
$command->setMongo($container->get(CONTAINER_CONFIG_MONGO));
$command->setSqlite($container->get(SQLite3::class));
$commands[] = $command;
$command = new \app\commands\ReindexHompages();
$command->setMongo($container->get(CONTAINER_CONFIG_MONGO));
$commands[] = $command;
$command = new \app\commands\ExtractIndexedContent();
$command->setMongo($container->get(CONTAINER_CONFIG_MONGO));
$commands[] = $command;
$command = new \app\commands\AddWebsite();
$command->setMongo($container->get(CONTAINER_CONFIG_MONGO));
$commands[] = $command;


$application = new Application();
$application->addCommands($commands);

$application->run();