<?php

use App\Common\MessageBus\Factories\MessageBusFactory;
use App\Common\MessageBus\Factories\WorkerFactory;
use App\Common\MessageBus\Handlers\Processors\GithubContributorsProcessor;
use App\Common\MessageBus\Handlers\Processors\GithubFollowersParsedProcessor;
use App\Common\MessageBus\Handlers\Processors\GithubProfileParsedProcessor;
use App\Common\MessageBus\Handlers\Processors\MetaInfoProcessor;
use App\Common\MessageBus\Handlers\Processors\RssFeedProcessor;
use App\Common\MessageBus\Handlers\Processors\WebFeedSeekerProcessor;
use App\Common\MessageBus\Messages\Processors\GithubContributorsToProcessMessage;
use App\Common\MessageBus\Messages\Processors\GithubFollowersToProcessMessage;
use App\Common\MessageBus\Messages\Processors\GithubProfileParsedToProcessMessage;
use App\Common\MessageBus\Messages\Processors\WebsiteHistoryMessage;
use App\Common\MessageBus\Messages\Processors\XmlRssContentToProcessMessage;
use App\Common\Services\Parsers\HtmlParserService;
use App\Common\Services\Parsers\XmlRssParserService;
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
$container = require __DIR__ . '/../config/container.php';

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
        new WebFeedSeekerProcessor(\getenv('REDIS_QUEUE_CONSUMER'), $crawlersBus, new HtmlParserService()),
        [
            'from_transport' => WebFeedSeekerProcessor::TRANSPORT,
        ]
    )
)->addHandler(
    XmlRssContentToProcessMessage::class,
    new HandlerDescriptor(
        new RssFeedProcessor(\getenv('REDIS_QUEUE_CONSUMER'), $persistorBus, new XmlRssParserService()),
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
