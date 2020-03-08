<?php

use app\messageBus\factories\WorkerFactory;
use app\messageBus\handlers\persistors\GithubFollowerParsedPersistor;
use app\messageBus\handlers\persistors\GithubProfileParsedPersistor;
use app\messageBus\handlers\persistors\GithubProfileRepoMetaForGroupPersistor;
use app\messageBus\handlers\persistors\NewGithubProfilePersistor;
use app\messageBus\handlers\persistors\NewWebsitePersistor;
use app\messageBus\handlers\persistors\RssItemElasticPersistor;
use app\messageBus\handlers\persistors\WebsiteIndexHistoryPersistor;
use app\messageBus\handlers\persistors\WebsiteMetaInfoPersistor;
use app\messageBus\messages\persistors\GithubFollowerParsedToPersistMessage;
use app\messageBus\messages\persistors\GithubProfileParsedToPersistMessage;
use app\messageBus\messages\persistors\GithubProfileRepoMetaForGroupToPersistMessage;
use app\messageBus\messages\persistors\NewGithubProfileToPersistMessage;
use app\messageBus\messages\persistors\NewWebsiteToPersistMessage;
use app\messageBus\messages\persistors\RssItemToPersist;
use app\messageBus\messages\persistors\WebsiteFetchedPageToPersistMessage;
use app\messageBus\messages\persistors\WebsiteMetaInfoMessage;
use app\messageBus\repositories\GithubProfileRepository;
use app\messageBus\repositories\WebsiteGroupRepository;
use app\messageBus\repositories\WebsiteIndexHistoryRepository;
use app\modules\web\ProfileRepository;
use app\services\github\GithubProfileService;
use app\services\website\WebsiteGroupService;
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
/** @var \Elasticsearch\Client $elastic */
$elastic = $container->get(CONTAINER_CONFIG_ELASTIC);
//$elastic->create(['index' => 'website_rss_item']);//todo: remove from here when architecture is established
$cache = $container->get(CONTAINER_CONFIG_REDIS_CACHE);
$logger = $container->get(CONTAINER_CONFIG_LOGGER);
$githubProfileService = new GithubProfileService(new GithubProfileRepository($mongo), $logger, $cache);
$groupService = new WebsiteGroupService(new WebsiteGroupRepository($mongo), $cache);
/** @var \Symfony\Component\Messenger\MessageBusInterface $processorsBus */
$processorsBus = $container->get(CONTAINER_CONFIG_REDIS_STREAM_PROCESSORS);
/** @var \Symfony\Component\Messenger\MessageBusInterface $persistorsBus */
$crawlersBus = $container->get(CONTAINER_CONFIG_REDIS_STREAM_CRAWLERS);
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

)->addHandler(
    WebsiteFetchedPageToPersistMessage::class,
    new HandlerDescriptor(
        new WebsiteIndexHistoryPersistor(\getenv('REDIS_QUEUE_CONSUMER'), new WebsiteIndexHistoryRepository($mongo), $processorsBus),
        [
            'from_transport' => WebsiteMetaInfoPersistor::TRANSPORT,
        ]
    )
)->addHandler(
    NewWebsiteToPersistMessage::class,
    new HandlerDescriptor(
        new NewWebsitePersistor(
            \getenv('REDIS_QUEUE_CONSUMER'),
            new ProfileRepository($mongo),
            $crawlersBus,
            $githubProfileService,
            $groupService
        ),
        [
            'from_transport' => WebsiteMetaInfoPersistor::TRANSPORT,
        ]
    )
)->addHandler(
    RssItemToPersist::class,
    new HandlerDescriptor(
        new RssItemElasticPersistor(\getenv('REDIS_QUEUE_CONSUMER'), $elastic),
        [
            'from_transport' => RssItemElasticPersistor::TRANSPORT,
        ]
    )
)->addHandler(
    NewGithubProfileToPersistMessage::class,
    new HandlerDescriptor(
        new NewGithubProfilePersistor(\getenv('REDIS_QUEUE_CONSUMER'), $githubProfileService, $crawlersBus),
        [
            'from_transport' => NewGithubProfilePersistor::TRANSPORT,
        ]
    )
)->addHandler(
    GithubProfileParsedToPersistMessage::class,
    new HandlerDescriptor(
        new GithubProfileParsedPersistor(\getenv('REDIS_QUEUE_CONSUMER'), new GithubProfileRepository($mongo)),
        [
            'from_transport' => GithubProfileParsedPersistor::TRANSPORT,
        ]
    )
)->addHandler(
    GithubFollowerParsedToPersistMessage::class,
    new HandlerDescriptor(
        new GithubFollowerParsedPersistor(\getenv('REDIS_QUEUE_CONSUMER'), new GithubProfileRepository($mongo)),
        [
            'from_transport' => GithubFollowerParsedPersistor::TRANSPORT,
        ]
    )
)->addHandler(
    GithubProfileRepoMetaForGroupToPersistMessage::class,
    new HandlerDescriptor(
        new GithubProfileRepoMetaForGroupPersistor(\getenv('REDIS_QUEUE_CONSUMER'), $groupService),
        [
            'from_transport' => GithubProfileRepoMetaForGroupPersistor::TRANSPORT,
        ]
    )
);
$worker = WorkerFactory::createExceptionHandlingWorker($receivers, $factory->buildMessageBus(), $logger, $container->get(CONTAINER_CONFIG_METRICS));
unset($factory);
$worker->run();
