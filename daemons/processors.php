<?php

use Symfony\Component\Messenger\Transport\RedisExt\RedisReceiver;
use Symfony\Component\Messenger\Transport\RedisExt\RedisTransport;

if (PHP_SAPI !== 'cli') {
    throw new \Exception('The script is only for cli');
}

require_once 'vendor/autoload.php';

if ($argc < 2) {
    throw new \Exception('consumer name is required');
}
\putenv("REDIS_QUEUE_CONSUMER=$argv[1]");
/** @var \Slim\Container $container */
$container = require __DIR__ . '/../src/config/container.php';

/** @var RedisTransport $transport */
$connection = $container->get(CONTAINER_CONFIG_REDIS_STREAM_CONNECTION_PROCESSORS);
$receivers = [
    CONTAINER_CONFIG_REDIS_STREAM_TRANSPORT_PROCESSORS => new RedisReceiver(
        $connection,
        $container->get(CONTAINER_CONFIG_REDIS_STREAM_SERIALIZER)
    )
];
$factory = new \app\messageBus\factories\MessageBusFactory($container);
// add only /processors handlers

$worker = new \Symfony\Component\Messenger\Worker($receivers, $factory->buildMessageBus());
unset($factory);
$worker->run();
