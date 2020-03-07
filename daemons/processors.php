<?php

use app\messageBus\factories\MessageBusFactory;
use app\messageBus\factories\WorkerFactory;
use app\messageBus\handlers\processors\GithubContributorsProcessor;
use app\messageBus\handlers\processors\GithubFollowersParsedProcessor;
use app\messageBus\handlers\processors\GithubProfileParsedProcessor;
use app\messageBus\handlers\processors\MetaInfoProcessor;
use app\messageBus\handlers\processors\RssFeedProcessor;
use app\messageBus\handlers\processors\RssFeedSeekerProcessor;
use app\messageBus\messages\processors\GithubContributorsToProcessMessage;
use app\messageBus\messages\processors\GithubFollowersToProcessMessage;
use app\messageBus\messages\processors\GithubProfileParsedToProcessMessage;
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
            'from_transport' => RssFeedSeekerProcessor::TRANSPORT,
        ]
    )
)->addHandler(
    XmlRssContentToProcessMessage::class,
    new HandlerDescriptor(
        new RssFeedProcessor(\getenv('REDIS_QUEUE_CONSUMER'), $persistorBus),
        [
            'from_transport' => RssFeedProcessor::TRANSPORT,
        ]
    )
)->addHandler(
    GithubProfileParsedToProcessMessage::class,
    new HandlerDescriptor(
        new GithubProfileParsedProcessor(\getenv('REDIS_QUEUE_CONSUMER'), $persistorBus, $crawlersBus),
        [
            'from_transport' => GithubProfileParsedProcessor::TRANSPORT,
        ]
    )
)->addHandler(
    GithubFollowersToProcessMessage::class,
    new HandlerDescriptor(
        new GithubFollowersParsedProcessor(\getenv('REDIS_QUEUE_CONSUMER'), $persistorBus),
        [
            'from_transport' => GithubFollowersParsedProcessor::TRANSPORT,
        ]
    )
)->addHandler(
    GithubContributorsToProcessMessage::class,
    new HandlerDescriptor(
        new GithubContributorsProcessor(\getenv('REDIS_QUEUE_CONSUMER'), $persistorBus),
        [
            'from_transport' => GithubContributorsProcessor::TRANSPORT,
        ]
    )
);

$worker = WorkerFactory::createExceptionHandlingWorker($receivers, $factory->buildMessageBus(), $container->get(CONTAINER_CONFIG_LOGGER), $container->get(CONTAINER_CONFIG_METRICS));
unset($factory);
$worker->run();
