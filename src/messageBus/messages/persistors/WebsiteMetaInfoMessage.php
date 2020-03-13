<?php

namespace app\messageBus\messages\persistors;

use app\messageBus\messages\MessageInterface;
use MongoDB\BSON\ObjectId;

class WebsiteMetaInfoMessage implements MessageInterface
{
    /** @var ObjectId */
    private $websiteId;
    /** @var ObjectId */
    private $historyIndexId;
    /** @var null|string */
    private $title;
    /** @var null|string */
    private $description;

    public function __construct(ObjectId $websiteId, ObjectId $historyIndexId, ?string $title, ?string $description)
    {
        $this->websiteId = $websiteId;
        $this->historyIndexId = $historyIndexId;
        $this->title = $title;
        $this->description = $description;
    }

    public function getWebsiteId(): ObjectId
    {
        return $this->websiteId;
    }

    public function getHistoryIndexId(): ObjectId
    {
        return $this->historyIndexId;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }
}
