<?php

use App\Common\MessageBus\Factories\MessageBusFactory;
use App\Common\MessageBus\Factories\WorkerFactory;
use App\Common\MessageBus\Handlers\Crawlers\GithubContributorsCrawler;
use App\Common\MessageBus\Handlers\Crawlers\GithubFollowersCrawler;
use App\Common\MessageBus\Handlers\Crawlers\GithubProfileCrawler;
use App\Common\MessageBus\Handlers\Crawlers\PageFetcherCrawler;
use App\Common\MessageBus\Handlers\Crawlers\RssFeedFetcherCrawler;
use App\Common\MessageBus\Messages\Crawlers\GithubContributorsToCrawlMessage;
use App\Common\MessageBus\Messages\Crawlers\GithubFollowersToCrawlMessage;
use App\Common\MessageBus\Messages\Crawlers\NewGithubProfileToCrawlMessage;
use App\Common\MessageBus\Messages\Crawlers\NewWebsiteToCrawlMessage;
use App\Common\MessageBus\Messages\Crawlers\RssFeedToCrawlMessage;
use App\Common\Services\HttpClient;
use App\Common\Services\Github\GithubApiFetcher;
use App\Common\Services\Website\WebsiteFetcher;
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
$connection = $container->get(CONTAINER_CONFIG_REDIS_STREAM_CONNECTION_CRAWLERS);
$receivers = [
    CONTAINER_CONFIG_REDIS_STREAM_TRANSPORT_CRAWLERS => new RedisReceiver(
        $connection,
        $container->get(CONTAINER_CONFIG_REDIS_STREAM_SERIALIZER)
    )
];
$websiteFetcher = new WebsiteFetcher(new HttpClient('hpdb-bot-c/0.1'), \getenv('DAEMONS_WEBSITE_FETCHER_MAX_SIZE_BYTES'));
$apiFetcher = new GithubApiFetcher(new HttpClient('hpdb-bot-a/0.1'), \getenv('GITHUB_API_AUTH'));
/** @var \Symfony\Component\Messenger\MessageBusInterface $persistorBus */
$persistorBus = $container->get(CONTAINER_CONFIG_REDIS_STREAM_PERSISTORS);
/** @var \Symfony\Component\Messenger\MessageBusInterface $processorsBus */
$processorsBus = $container->get(CONTAINER_CONFIG_REDIS_STREAM_PROCESSORS);
$factory = new MessageBusFactory($container);
// add only /crawlers handlers
$factory->addHandler(
    NewWebsiteToCrawlMessage::class,
    new HandlerDescriptor(
        new PageFetcherCrawler(\getenv('REDIS_QUEUE_CONSUMER'), $websiteFetcher, $persistorBus),
        [
            'from_transport' => PageFetcherCrawler::TRANSPORT
        ]
    )
)->addHandler(
    RssFeedToCrawlMessage::class,
    new HandlerDescriptor(
        new RssFeedFetcherCrawler(\getenv('REDIS_QUEUE_CONSUMER'), $websiteFetcher, $processorsBus),
        [
            'from_transport' => PageFetcherCrawler::TRANSPORT
        ]
    )
)->addHandler(
    NewGithubProfileToCrawlMessage::class,
    new HandlerDescriptor(
        new GithubProfileCrawler(\getenv('REDIS_QUEUE_CONSUMER'), $apiFetcher, $processorsBus),
        [
            'from_transport' => GithubProfileCrawler::TRANSPORT
        ]
    )
)->addHandler(
    GithubFollowersToCrawlMessage::class,
    new HandlerDescriptor(
        new GithubFollowersCrawler(\getenv('REDIS_QUEUE_CONSUMER'), $apiFetcher, $processorsBus),
        [
            'from_transport' => GithubFollowersCrawler::TRANSPORT
        ]
    )
)->addHandler(
    GithubContributorsToCrawlMessage::class,
    new HandlerDescriptor(
        new GithubContributorsCrawler(\getenv('REDIS_QUEUE_CONSUMER'), $apiFetcher, $processorsBus, $persistorBus),
        [
            'from_transport' => GithubContributorsCrawler::TRANSPORT
        ]
    )
);
$worker = WorkerFactory::createExceptionHandlingWorker($receivers, $factory->buildMessageBus(), $container->get(CONTAINER_CONFIG_LOGGER), $container->get(CONTAINER_CONFIG_METRICS));
unset($factory);
$worker->run();
