<?php

declare(strict_types=1);

namespace App\Common\Services\Scheduled;

use App\Common\Exceptions\Scheduled\ScheduledMessageExists;
use App\Common\MessageBus\Messages\MessageInterface;
use App\Common\Models\ScheduledMessage;
use App\Common\Repositories\ScheduledMessageRepository;
use Illuminate\Support\LazyCollection;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

class ScheduledMessageService
{
    /** @var ScheduledMessageRepository */
    private $repository;
    private $serializer;

    public function __construct(ScheduledMessageRepository $repository, Base64Serializer $serializer)
    {
        $this->repository = $repository;
        $this->serializer = $serializer;
    }

    /**
     * @return ScheduledMessage[]
     */
    public function getMessagesBatchToRun(\DateTimeInterface $dateTime): LazyCollection
    {
        return $this->repository->getBatchToRun($dateTime);
    }

    public function ack(ObjectId $id): bool
    {
        return $this->repository->delete($id);
    }

    public function queue(MessageInterface $messageToDeffer, \DateTimeInterface $runAt): ScheduledMessage
    {
        $message = new ScheduledMessage();
        $message->run_at = new UTCDateTime($runAt->getTimestamp() * 1000);
        $message->classname = \get_class($messageToDeffer);
        $message->serialized = $this->serializer->serialize($messageToDeffer);
        $message->status = ScheduledMessage::STATUS_READY;
        if ($this->repository->isDuplicateExists($message)) {
            throw new ScheduledMessageExists(json_encode($messageToDeffer));
        }
        $this->repository->save($message);

        return $message;
    }

    public function extractMessage(ScheduledMessage $scheduledMessage): MessageInterface
    {
        return $this->serializer->unserialize($scheduledMessage->serialized);
    }

    public function markAsTaken(ScheduledMessage $scheduledMessage): bool
    {
        $scheduledMessage->status = ScheduledMessage::STATUS_TAKEN;

        return $this->repository->save($scheduledMessage);
    }
}
