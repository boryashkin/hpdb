<?php

namespace App\Common\Repositories;

use App\Common\Models\ScheduledMessage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\LazyCollection;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

class ScheduledMessageRepository extends AbstractMongoRepository
{
    /**
     * @return ScheduledMessage|Model|null
     */
    public function getOne(ObjectId $id): ?ScheduledMessage
    {
        return ScheduledMessage::query()
            ->where('_id', '=', $id)
            ->first();
    }

    /**
     * @return ScheduledMessage[]|LazyCollection
     */
    public function getBatchToRun(\DateTimeInterface $afterDateTime): LazyCollection
    {
        return ScheduledMessage::query()
            ->useWritePdo()
            ->where('run_at', '<=', new UTCDateTime($afterDateTime->getTimestamp() * 1000))
            ->where('status', '=', ScheduledMessage::STATUS_READY)
            ->get()->lazy();
    }

    public function delete(ObjectId $id): bool
    {
        return (bool)ScheduledMessage::query()->where('_id', '=', $id)->delete();
    }

    public function save(ScheduledMessage $message): bool
    {
        return $message->save();
    }
}
