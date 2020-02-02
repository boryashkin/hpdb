
<?php

use app\messageBus\factories\WorkerFactory;
use app\messageBus\handlers\discoverers\GithubProfileDiscoverer;
use app\messageBus\messages\discoverers\GithubProfileMessage;
use Symfony\Component\Messenger\Handler\HandlerDescriptor;
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
$connection = $container->get(CONTAINER_CONFIG_REDIS_STREAM_CONNECTION_DISCOVERERS);
$receivers = [
    CONTAINER_CONFIG_REDIS_STREAM_TRANSPORT_DISCOVERERS => new RedisReceiver(
        $connection,
        $container->get(CONTAINER_CONFIG_REDIS_STREAM_SERIALIZER)
    )
];
/** @var \Symfony\Component\Messenger\MessageBusInterface $busCrawlers */
$busCrawlers = $container->get(CONTAINER_CONFIG_REDIS_STREAM_CRAWLERS);
$factory = new \app\messageBus\factories\MessageBusFactory($container);
// add only /discoveres handlers
$factory->addHandler(
    GithubProfileMessage::class,
    new HandlerDescriptor(
        new GithubProfileDiscoverer(\getenv('REDIS_QUEUE_CONSUMER'), $busCrawlers),
        [
            'from_transport' => GithubProfileDiscoverer::TRANSPORT
        ]
    )
);
$worker = WorkerFactory::createExceptionHandlingWorker($receivers, $factory->buildMessageBus(), $container->get(CONTAINER_CONFIG_LOGGER));
unset($factory);
$worker->run();