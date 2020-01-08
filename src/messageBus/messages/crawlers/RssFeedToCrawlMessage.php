<?php

namespace app\messageBus\messages\crawlers;

use app\messageBus\messages\MessageInterface;
use app\valueObjects\Url;
use MongoDB\BSON\ObjectId;

class RssFeedToCrawlMessage implements MessageInterface
{
    private $websiteId;
    private $url;

    public function __construct(ObjectId $websiteId, Url $url)
    {
        $this->websiteId = $websiteId;
        $this->url = $url;
    }

    public function getWebsiteId(): ObjectId
    {
        return $this->websiteId;
    }

    public function getUrl(): Url
    {
        return $this->url;
    }
}
