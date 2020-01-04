<?php

namespace app\messageBus\factories;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Worker;

class WorkerFactory
{
    public static function createExceptionHandlingWorker(array $receivers, MessageBusInterface $bus, LoggerInterface $logger)
    {
        $dispatcher = new EventDispatcher();
        $dispatcher->addListener(WorkerMessageFailedEvent::class, function (WorkerMessageFailedEvent $e) use ($logger) {
            $logger->error(
                \implode(
                    ', ',
                    [
                        $e->getReceiverName(),
                        \get_class($e->getEnvelope()->getMessage()),
                        $e->getThrowable()->getMessage()
                    ]
                )
            );
        });

        return new Worker($receivers, $bus, $dispatcher);
    }
}
