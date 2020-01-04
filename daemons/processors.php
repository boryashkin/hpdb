<?php

use app\messageBus\factories\MessageBusFactory;
use app\messageBus\factories\WorkerFactory;
use app\messageBus\handlers\processors\MetaInfoProcessor;
use app\messageBus\messages\processors\WebsiteHistoryMessage;
use app\messageBus\repositories\WebsiteIndexHistoryRepository;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
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
$connection = $container->get(CONTAINER_CONFIG_REDIS_STREAM_CONNECTION_PROCESSORS);
$receivers = [
    CONTAINER_CONFIG_REDIS_STREAM_TRANSPORT_PROCESSORS => new RedisReceiver(
        $connection,
        $container->get(CONTAINER_CONFIG_REDIS_STREAM_SERIALIZER)
    )
];
/** @var \Jenssegers\Mongodb\Connection $mongo */
$mongo = $container->get(CONTAINER_CONFIG_MONGO);
/** @var \Symfony\Component\Messenger\MessageBusInterface $persistorBus */
$persistorBus = $container->get(CONTAINER_CONFIG_REDIS_STREAM_PERSISTORS);

$factory = new MessageBusFactory($container);
// add only /processors handlers
$factory->addHandler(
    WebsiteHistoryMessage::class,
    new HandlerDescriptor(
        new MetaInfoProcessor(\getenv('REDIS_QUEUE_CONSUMER'), new WebsiteIndexHistoryRepository($mongo), $persistorBus),
        [
            'from_transport' => MetaInfoProcessor::TRANSPORT,
        ]
    )
)->addHandler(
    WorkerMessageFailedEvent::class,
    new HandlerDescriptor(
        function (WorkerMessageFailedEvent $e) {
            echo "fail \n";
            echo $e->getThrowable()->getMessage();
        }
    )
);

$worker = WorkerFactory::createExceptionHandlingWorker($receivers, $factory->buildMessageBus(), $container->get(CONTAINER_CONFIG_LOGGER));
unset($factory);
$worker->run();
