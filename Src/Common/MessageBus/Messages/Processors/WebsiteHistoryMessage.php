<?php

namespace App\Common\MessageBus\Messages\Processors;

use App\Common\MessageBus\Messages\MessageInterface;
use App\Common\ValueObjects\Url;
use MongoDB\BSON\ObjectId;

class WebsiteHistoryMessage implements MessageInterface
{
    private $websiteId;
    private $indexHistoryId;
    private $url;
    private $initialEncoding;
    private $content;

    public function __construct(ObjectId $websiteId, ObjectId $indexHistoryId, Url $url, string $content, ?string $initialEncoding)
    {
        $this->websiteId = $websiteId;
        $this->indexHistoryId = $indexHistoryId;
        $this->url = $url;
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

    public function getUrl(): Url
    {
        return $this->url;
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
