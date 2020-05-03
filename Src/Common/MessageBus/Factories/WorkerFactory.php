<?php

namespace App\Common\MessageBus\Factories;

use App\Common\Services\MetricsCollector;
use Illuminate\Database\Events\QueryExecuted;
use MongoDB\Driver\Exception\InvalidArgumentException;
use MongoDB\Driver\Exception\UnexpectedValueException;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\Event\WorkerMessageHandledEvent;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Worker;

class WorkerFactory
{
    public static function createExceptionHandlingWorker(
        array $receivers,
        MessageBusInterface $bus,
        LoggerInterface $logger,
        MetricsCollector $metrics,
        EventDispatcherInterface $dispatcher
    ): Worker
    {
        $dispatcher->addListener(WorkerMessageFailedEvent::class, function (WorkerMessageFailedEvent $e) use ($metrics) {
            $className = \get_class($e->getEnvelope() ? $e->getEnvelope()->getMessage() : $e->getEnvelope());
            $metrics->getOrRegisterCounter(
                MetricsCollector::NS_CLI_BUS,
                MetricsCollector::getNamespaceFromClassName($className) . MetricsCollector::SUFFIX_ERROR,
                ''
            )->inc();
        });
        $dispatcher->addListener(WorkerMessageFailedEvent::class, function (WorkerMessageFailedEvent $e) use ($logger) {
            $message = $e->getThrowable()->getMessage();
            if ($e->getThrowable() instanceof HandlerFailedException) {
                $exE = $e->getThrowable()->getPrevious();
                //mongodb message about "invalid UTF-8" will unnecessary throw entire html
                if (
                    $exE instanceof UnexpectedValueException
                    || $exE instanceof InvalidArgumentException
                ) {
                    $message = substr($message, 0, 100);
                }
            }

            $logger->error(
                \implode(
                    ', ',
                    [
                        $e->getReceiverName(),
                        \get_class($e->getEnvelope() ? $e->getEnvelope()->getMessage() : $e->getEnvelope()),
                        \get_class($e->getThrowable()),
                        $message,
                    ]
                )
            );
        });
        $dispatcher->addListener(WorkerMessageHandledEvent::class, function (WorkerMessageHandledEvent $e) use ($metrics) {
            $className = \get_class($e->getEnvelope() ? $e->getEnvelope()->getMessage() : $e->getEnvelope());
            $metrics->getOrRegisterCounter(
                MetricsCollector::NS_CLI_BUS,
                MetricsCollector::getNamespaceFromClassName($className) . MetricsCollector::SUFFIX_TICK,
                ''
            )->inc();
        });
        $dispatcher->addListener(WorkerMessageHandledEvent::class, function (WorkerMessageHandledEvent $e) use ($logger) {
            static $cnt = 1;
            $logger->info(
                \implode(
                    ', ',
                    [
                        $cnt++,
                        date(DATE_ATOM),
                        $e->getReceiverName(),
                        \get_class($e->getEnvelope() ? $e->getEnvelope()->getMessage() : $e->getEnvelope()),
                    ]
                )
            );
        });
        self::addDbQueryDispatcher($dispatcher, $metrics);

        return new Worker($receivers, $bus, $dispatcher);
    }

    private static function addDbQueryDispatcher(EventDispatcherInterface $dispatcher, MetricsCollector $metrics): void
    {
        $dispatcher->addListener(
            QueryExecuted::class,
            static function (QueryExecuted $event) use ($metrics) {
                $query = substr($event->sql, 0, strpos($event->sql, '('));
                $metrics
                    ->getOrRegisterHistogram(
                        MetricsCollector::NS_CLI_BUS_MONGO,
                        MetricsCollector::getNamespaceFromString($query),
                        ''
                    )
                    ->observe($event->time);
            }
        );

        return;
    }
}
