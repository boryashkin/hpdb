<?php

namespace App\Common\MessageBus\Handlers\Persistors;

use App\Common\MessageBus\Messages\Persistors\ScheduledMessageToPersistMessage;
use App\Common\Services\Scheduled\ScheduledMessageService;

class ScheduledMessagePersistor implements PersistorInterface
{
    private $name;
    private $service;

    public function __construct(string $name, ScheduledMessageService $scheduledMessageService)
    {
        $this->name = $name;
        $this->service = $scheduledMessageService;
    }

    public function __invoke(ScheduledMessageToPersistMessage $message)
    {
        $this->service->queue($message->getMessage(), $message->getRunAt());
    }
}
