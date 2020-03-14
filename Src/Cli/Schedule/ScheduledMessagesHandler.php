<?php

declare(strict_types=1);

namespace App\Cli\Schedule;

use App\Common\Services\MetricsCollector;
use App\Common\Services\Scheduled\ScheduledMessageService;
use MongoDB\BSON\ObjectId;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class ScheduledMessagesHandler
{
    private $scheduledMessageService;
    private $bus;
    private $metricsCollector;
    private $logger;

    public function __construct(
        ScheduledMessageService $scheduledMessageService,
        MessageBusInterface $bus,
        MetricsCollector $metricsCollector,
        LoggerInterface $logger
    )
    {
        $this->scheduledMessageService = $scheduledMessageService;
        $this->bus = $bus;
        $this->metricsCollector = $metricsCollector;
        $this->logger = $logger;
    }

    public function __invoke()
    {
        $this->logger->debug('tick');
        $this->invokeTick();

        foreach ($this->scheduledMessageService->getMessagesBatchToRun(new \DateTime()) as $scheduledMessage) {
            $taken = $this->scheduledMessageService->markAsTaken($scheduledMessage);
            if (!$taken) {
                $this->logger->info("[ScheduledMessagesHandler] Failed to take: {$scheduledMessage->_id}");
                continue;
            }
            $message = $this->scheduledMessageService->extractMessage($scheduledMessage);
            $this->bus->dispatch($message);
            $result = $this->scheduledMessageService->ack(new ObjectId($scheduledMessage->_id));
            if (!$result) {
                $this->logger->info("[ScheduledMessagesHandler] Failed to ack: {$scheduledMessage->_id}");
            }
        }
        $this->logger->debug('end');
    }

    private function invokeTick(): void
    {
        $this->metricsCollector->getOrRegisterCounter(
            MetricsCollector::NS_CLI_BUS,
            MetricsCollector::getNamespaceFromClassName(ScheduledMessageService::class)
            . MetricsCollector::SUFFIX_TICK,
            ''
        )->inc();
    }
}
