<?php

require __DIR__.'/vendor/autoload.php';

use Symfony\Component\Console\Application;


$container = require 'src/config/container.php';
$command = new \app\commands\MigrateSqliteToMongo();
$command->setMongo($container->get(CONTAINER_CONFIG_MONGO));
$command->setSqlite($container->get(SQLite3::class));


$application = new Application();
$application->add($command);

$application->run();