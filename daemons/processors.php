<?php

use app\messageBus\factories\MessageBusFactory;
use app\messageBus\factories\WorkerFactory;
use app\messageBus\handlers\processors\MetaInfoProcessor;
use app\messageBus\handlers\processors\RssFeedProcessor;
use app\messageBus\handlers\processors\RssFeedSeekerProcessor;
use app\messageBus\messages\processors\WebsiteHistoryMessage;
use app\messageBus\messages\processors\XmlRssContentToProcessMessage;
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
/** @var \Symfony\Component\Messenger\MessageBusInterface $persistorBus */
$persistorBus = $container->get(CONTAINER_CONFIG_REDIS_STREAM_PERSISTORS);
/** @var \Symfony\Component\Messenger\MessageBusInterface $crawlersBus */
$crawlersBus = $container->get(CONTAINER_CONFIG_REDIS_STREAM_CRAWLERS);

$factory = new MessageBusFactory($container);
// add only /processors handlers
$factory->addHandler(
    WebsiteHistoryMessage::class,
    new HandlerDescriptor(
        new MetaInfoProcessor(\getenv('REDIS_QUEUE_CONSUMER'), $persistorBus),
        [
            'from_transport' => MetaInfoProcessor::TRANSPORT,
        ]
    )
)->addHandler(
    WebsiteHistoryMessage::class,
    new HandlerDescriptor(
        new RssFeedSeekerProcessor(\getenv('REDIS_QUEUE_CONSUMER'), $crawlersBus),
        [
            'from_transport' => MetaInfoProcessor::TRANSPORT,
        ]
    )
)->addHandler(
    XmlRssContentToProcessMessage::class,
    new HandlerDescriptor(
        new RssFeedProcessor(\getenv('REDIS_QUEUE_CONSUMER'), $persistorBus),
        [
            'from_transport' => MetaInfoProcessor::TRANSPORT,
        ]
    )
);

$worker = WorkerFactory::createExceptionHandlingWorker($receivers, $factory->buildMessageBus(), $container->get(CONTAINER_CONFIG_LOGGER));
unset($factory);
$worker->run();
