<?php

use app\messageBus\factories\MessageBusFactory;
use app\messageBus\factories\WorkerFactory;
use app\messageBus\handlers\crawlers\GithubFollowersCrawler;
use app\messageBus\handlers\crawlers\GithubProfileCrawler;
use app\messageBus\handlers\crawlers\PageFetcherCrawler;
use app\messageBus\handlers\crawlers\RssFeedFetcherCrawler;
use app\messageBus\messages\crawlers\GithubFollowersToCrawlMessage;
use app\messageBus\messages\crawlers\NewGithubProfileToCrawlMessage;
use app\messageBus\messages\crawlers\NewWebsiteToCrawlMessage;
use app\messageBus\messages\crawlers\RssFeedToCrawlMessage;
use app\services\website\WebsiteFetcher;
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
$connection = $container->get(CONTAINER_CONFIG_REDIS_STREAM_CONNECTION_CRAWLERS);
$receivers = [
    CONTAINER_CONFIG_REDIS_STREAM_TRANSPORT_CRAWLERS => new RedisReceiver(
        $connection,
        $container->get(CONTAINER_CONFIG_REDIS_STREAM_SERIALIZER)
    )
];
$fetcher = new WebsiteFetcher(new \app\services\HttpClient('hpdb-bot-c/0.1'), \getenv('DAEMONS_WEBSITE_FETCHER_MAX_SIZE_BYTES'));
/** @var \Symfony\Component\Messenger\MessageBusInterface $persistorBus */
$persistorBus = $container->get(CONTAINER_CONFIG_REDIS_STREAM_PERSISTORS);
/** @var \Symfony\Component\Messenger\MessageBusInterface $processorsBus */
$processorsBus = $container->get(CONTAINER_CONFIG_REDIS_STREAM_PROCESSORS);
$factory = new MessageBusFactory($container);
// add only /crawlers handlers
$factory->addHandler(
    NewWebsiteToCrawlMessage::class,
    new HandlerDescriptor(
        new PageFetcherCrawler(\getenv('REDIS_QUEUE_CONSUMER'), $fetcher, $persistorBus),
        [
            'from_transport' => PageFetcherCrawler::TRANSPORT
        ]
    )
)->addHandler(
    RssFeedToCrawlMessage::class,
    new HandlerDescriptor(
        new RssFeedFetcherCrawler(\getenv('REDIS_QUEUE_CONSUMER'), $fetcher, $processorsBus),
        [
            'from_transport' => PageFetcherCrawler::TRANSPORT
        ]
    )
)->addHandler(
    NewGithubProfileToCrawlMessage::class,
    new HandlerDescriptor(
        new GithubProfileCrawler(\getenv('REDIS_QUEUE_CONSUMER'), $fetcher, $processorsBus),
        [
            'from_transport' => GithubProfileCrawler::TRANSPORT
        ]
    )
)->addHandler(
    GithubFollowersToCrawlMessage::class,
    new HandlerDescriptor(
        new GithubFollowersCrawler(\getenv('REDIS_QUEUE_CONSUMER'), $fetcher, $processorsBus),
        [
            'from_transport' => GithubFollowersCrawler::TRANSPORT
        ]
    )
);
$worker = WorkerFactory::createExceptionHandlingWorker($receivers, $factory->buildMessageBus(), $container->get(CONTAINER_CONFIG_LOGGER));
unset($factory);
$worker->run();
