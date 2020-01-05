<?php

namespace app\messageBus\messages\processors;

use app\messageBus\messages\MessageInterface;
use MongoDB\BSON\ObjectId;

class WebsiteHistoryMessage implements MessageInterface
{
    private $websiteId;
    private $indexHistoryId;
    private $initialEncoding;
    private $content;

    public function __construct(ObjectId $websiteId, ObjectId $indexHistoryId, string $content, ?string $initialEncoding)
    {
        $this->websiteId = $websiteId;
        $this->indexHistoryId = $indexHistoryId;
        $this->initialEncoding = $initialEncoding;
        $this->content = $content;
    }

    public function getWebsiteId(): ObjectId
    {
        return $this->websiteId;
    }

    public function getIndexHistoryId(): ObjectId
    {
        return $this->indexHistoryId;
    }

    public function getInitialEncoding(): ?string
    {
        return $this->initialEncoding;
    }

    public function getContent(): string
    {
        return $this->content;
    }
}
