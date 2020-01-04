<?php

namespace app\messageBus\messages\processors;

use app\messageBus\messages\MessageInterface;
use MongoDB\BSON\ObjectId;

class WebsiteHistoryMessage implements MessageInterface
{
    private $indexHistoryId;

    public function __construct(ObjectId $indexHistoryId)
    {
        $this->indexHistoryId = $indexHistoryId;
    }

    public function getIndexHistoryId(): ObjectId
    {
        return $this->indexHistoryId;
    }
}
