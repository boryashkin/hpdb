<?php

use App\Common\MessageBus\Factories\WorkerFactory;
use App\Common\MessageBus\Handlers\Persistors\GithubFollowerParsedPersistor;
use App\Common\MessageBus\Handlers\Persistors\GithubProfileParsedPersistor;
use App\Common\MessageBus\Handlers\Persistors\GithubProfileRepoMetaForGroupPersistor;
use App\Common\MessageBus\Handlers\Persistors\NewGithubProfilePersistor;
use App\Common\MessageBus\Handlers\Persistors\NewWebsitePersistor;
use App\Common\MessageBus\Handlers\Persistors\RssFeedMetaInfoPersistor;
use App\Common\MessageBus\Handlers\Persistors\RssItemElasticPersistor;
use App\Common\MessageBus\Handlers\Persistors\ScheduledMessagePersistor;
use App\Common\MessageBus\Handlers\Persistors\WebsiteIndexHistoryPersistor;
use App\Common\MessageBus\Handlers\Persistors\WebsiteMetaInfoPersistor;
use App\Common\MessageBus\Messages\Persistors\GithubFollowerParsedToPersistMessage;
use App\Common\MessageBus\Messages\Persistors\GithubProfileParsedToPersistMessage;
use App\Common\MessageBus\Messages\Persistors\GithubProfileRepoMetaForGroupToPersistMessage;
use App\Common\MessageBus\Messages\Persistors\NewGithubProfileToPersistMessage;
use App\Common\MessageBus\Messages\Persistors\NewWebsiteToPersistMessage;
use App\Common\MessageBus\Messages\Persistors\RssFeedMetaInfoToPersist;
use App\Common\MessageBus\Messages\Persistors\RssItemToPersist;
use App\Common\MessageBus\Messages\Persistors\ScheduledMessageToPersistMessage;
use App\Common\MessageBus\Messages\Persistors\WebsiteFetchedPageToPersistMessage;
use App\Common\MessageBus\Messages\Persistors\WebsiteMetaInfoMessage;
use App\Common\Repositories\GithubProfileRepository;
use App\Common\Repositories\ScheduledMessageRepository;
use App\Common\Repositories\WebFeedRepository;
use App\Common\Repositories\WebsiteGroupRepository;
use App\Common\Repositories\WebsiteIndexHistoryRepository;
use App\Common\Repositories\ProfileRepository;
use App\Common\Services\Github\GithubProfileService;
use App\Common\Services\Scheduled\Base64Serializer;
use App\Common\Services\Scheduled\ScheduledMessageService;
use App\Common\Services\Website\WebsiteGroupService;
use App\Common\Services\Website\WebsiteService;
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
$scheduledSerializer = $container->get(Base64Serializer::class);
$githubProfileService = new GithubProfileService(new GithubProfileRepository($mongo), $logger, $cache);
$profileRepository = new ProfileRepository($mongo);
$groupService = new WebsiteGroupService(new WebsiteGroupRepository($mongo), $cache);
/** @var \Symfony\Component\Messenger\MessageBusInterface $processorsBus */
$processorsBus = $container->get(CONTAINER_CONFIG_REDIS_STREAM_PROCESSORS);
/** @var \Symfony\Component\Messenger\MessageBusInterface $persistorsBus */
$crawlersBus = $container->get(CONTAINER_CONFIG_REDIS_STREAM_CRAWLERS);
$factory = new \App\Common\MessageBus\Factories\MessageBusFactory($container);
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
            $profileRepository,
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
        new RssItemElasticPersistor(\getenv('REDIS_QUEUE_CONSUMER'), new WebFeedRepository($elastic)),
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
)->addHandler(
    ScheduledMessageToPersistMessage::class,
    new HandlerDescriptor(
        new ScheduledMessagePersistor(
            \getenv('REDIS_QUEUE_CONSUMER'),
            new ScheduledMessageService(new ScheduledMessageRepository($mongo), $scheduledSerializer)
        ),
        [
            'from_transport' => ScheduledMessagePersistor::TRANSPORT,
        ]
    )
)->addHandler(
    RssFeedMetaInfoToPersist::class,
    new HandlerDescriptor(
        new RssFeedMetaInfoPersistor(
            \getenv('REDIS_QUEUE_CONSUMER'),
            new WebsiteService($profileRepository)
        ),
        [
            'from_transport' => RssFeedMetaInfoPersistor::TRANSPORT,
        ]
    )
);
$worker = WorkerFactory::createExceptionHandlingWorker($receivers, $factory->buildMessageBus(), $logger, $container->get(CONTAINER_CONFIG_METRICS));
unset($factory);
$worker->run();
