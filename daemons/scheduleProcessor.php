<?php

use App\Cli\Schedule\ScheduledMessagesHandler;
use App\Common\Repositories\ScheduledMessageRepository;
use App\Common\Services\MetricsCollector;
use App\Common\Services\Scheduled\Base64Serializer;
use App\Common\Services\Scheduled\ScheduledMessageService;
use Illuminate\Database\Events\QueryExecuted;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

if (PHP_SAPI !== 'cli') {
    throw new \Exception('The script is only for cli');
}

require_once 'vendor/autoload.php';

/** @var \Slim\Container $container */
$container = require __DIR__ . '/../config/container.php';

/** @var EventDispatcherInterface $dispatcher */
$dispatcher = $container->get(EventDispatcherInterface::class);
$mongo = $container->get(CONTAINER_CONFIG_MONGO);
$serializer = $container->get(Base64Serializer::class);
$scheduledService = new ScheduledMessageService(new ScheduledMessageRepository($mongo), $serializer);
$logger = $container->get(CONTAINER_CONFIG_LOGGER);
$metrics = $container->get(CONTAINER_CONFIG_METRICS);

//it's a sender bus. No handlers!
$factory = new \App\Common\MessageBus\Factories\MessageBusFactory($container);
$map = new \App\Common\MessageBus\Handlers\HandlersToMessageMapper();
foreach ($map->getCrawlersMessages() as $messageClass) {
    $factory->addSender($messageClass, CONTAINER_CONFIG_REDIS_STREAM_TRANSPORT_CRAWLERS);
}
foreach ($map->getDiscoverersMessages() as $messageClass) {
    $factory->addSender($messageClass, CONTAINER_CONFIG_REDIS_STREAM_TRANSPORT_DISCOVERERS);
}
foreach ($map->getPersistorsMessages() as $messageClass) {
    $factory->addSender($messageClass, CONTAINER_CONFIG_REDIS_STREAM_TRANSPORT_PERSISTORS);
}
foreach ($map->getProcessorsMessages() as $messageClass) {
    $factory->addSender($messageClass, CONTAINER_CONFIG_REDIS_STREAM_TRANSPORT_PROCESSORS);
};
$sendersBus = $factory->buildMessageBus();
unset($factory);

$loop = new React\EventLoop\StreamSelectLoop();
$scheduledHandler = new ScheduledMessagesHandler($scheduledService, $sendersBus, $metrics, $logger);
$loop->addPeriodicTimer(30, $scheduledHandler);

$dispatcher->addListener(
    QueryExecuted::class,
    static function (QueryExecuted $event) use ($metrics) {
        $query = substr($event->sql, 0, strpos($event->sql, '('));
        $metrics
            ->getOrRegisterHistogram(
                MetricsCollector::NS_CLI_SCHEDULE_MONGO,
                MetricsCollector::getNamespaceFromString($query),
                ''
            )
            ->observe($event->time);
    }
);

$loop->run();
