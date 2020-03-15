<?php

namespace App\Common\MessageBus\Messages\Crawlers;

use App\Common\Dto\Parsers\WebFeedItemDto;
use App\Common\MessageBus\Messages\MessageInterface;
use App\Common\ValueObjects\Url;
use MongoDB\BSON\ObjectId;

class WebFeedToCrawlMessage implements MessageInterface
{
    private $websiteId;
    private $feed;
    private $url;

    public function __construct(ObjectId $websiteId, WebFeedItemDto $feed, Url $feedUrl)
    {
        $this->websiteId = $websiteId;
        $this->feed = $feed;
        $this->url = $feedUrl;
    }

    public function getWebsiteId(): ObjectId
    {
        return $this->websiteId;
    }

    public function getUrl(): Url
    {
        return $this->url;
    }

    public function getFeed(): WebFeedItemDto
    {
        return $this->feed;
    }
}
