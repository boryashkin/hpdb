<?php

use app\messageBus\factories\WorkerFactory;
use app\messageBus\handlers\persistors\WebsiteMetaInfoPersistor;
use app\messageBus\messages\persistors\WebsiteMetaInfoMessage;
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
$connection = $container->get(CONTAINER_CONFIG_REDIS_STREAM_CONNECTION_PERSISTORS);
$receivers = [
    CONTAINER_CONFIG_REDIS_STREAM_TRANSPORT_PERSISTORS => new RedisReceiver(
        $connection,
        $container->get(CONTAINER_CONFIG_REDIS_STREAM_SERIALIZER)
    )
];
/** @var \Jenssegers\Mongodb\Connection $mongo */
$mongo = $container->get(CONTAINER_CONFIG_MONGO);

$factory = new \app\messageBus\factories\MessageBusFactory($container);
// add only /persistors handlers
$factory->addHandler(
    WebsiteMetaInfoMessage::class,
    new HandlerDescriptor(
        new WebsiteMetaInfoPersistor(\getenv('REDIS_QUEUE_CONSUMER'), $mongo),
        [
            'from_transport' => WebsiteMetaInfoPersistor::TRANSPORT,
        ]
    )

);
$worker = WorkerFactory::createExceptionHandlingWorker($receivers, $factory->buildMessageBus(), $container->get(CONTAINER_CONFIG_LOGGER));
unset($factory);
$worker->run();
