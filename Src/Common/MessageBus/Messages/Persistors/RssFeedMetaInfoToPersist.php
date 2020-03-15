<?php

namespace App\Common\MessageBus\Messages\Persistors;

use App\Common\Dto\Parsers\WebFeedItemDto;
use App\Common\MessageBus\Messages\MessageInterface;
use App\Common\ValueObjects\Url;
use DateTime;
use MongoDB\BSON\ObjectId;

class RssFeedMetaInfoToPersist implements MessageInterface
{
    /** @var ObjectId */
    private $websiteId;
    /** @var null|DateTime */
    private $pubDate;
    private $url;
    private $feedItemDto;

    public function __construct(ObjectId $websiteId, ?DateTime $pubDate, Url $url, WebFeedItemDto $feedItemDto)
    {
        $this->websiteId = $websiteId;
        $this->pubDate = $pubDate;
        $this->url = $url;
        $this->feedItemDto = $feedItemDto;
    }

    public function getWebsiteId(): ObjectId
    {
        return $this->websiteId;
    }

    public function getPubDate(): ?DateTime
    {
        return $this->pubDate;
    }

    public function getUrl(): Url
    {
        return $this->url;
    }

    public function getFeedItemDto(): WebFeedItemDto
    {
        return $this->feedItemDto;
    }
}
