<?php

namespace App\Common\MessageBus\Messages\Persistors;

use App\Common\MessageBus\Messages\MessageInterface;

class ScheduledMessageToPersistMessage implements MessageInterface
{
    private $message;
    /** @var \DateTimeInterface */
    private $runAt;

    public function __construct(MessageInterface $message, \DateTimeInterface $runAt)
    {
        $this->message = $message;
        $this->runAt = $runAt;
    }

    public function getMessage(): MessageInterface
    {
        return $this->message;
    }

    public function getRunAt(): \DateTimeInterface
    {
        return $this->runAt;
    }
}
