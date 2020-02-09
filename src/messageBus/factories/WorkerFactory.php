<?php

namespace app\messageBus\factories;

use MongoDB\Driver\Exception\InvalidArgumentException;
use MongoDB\Driver\Exception\UnexpectedValueException;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\Event\WorkerMessageHandledEvent;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Worker;

class WorkerFactory
{
    public static function createExceptionHandlingWorker(array $receivers, MessageBusInterface $bus, LoggerInterface $logger)
    {
        $dispatcher = new EventDispatcher();
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
        if (!ENV_PROD) {
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
        }

        return new Worker($receivers, $bus, $dispatcher);
    }
}
