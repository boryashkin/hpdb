<?php

namespace App\Common\MessageBus\Messages\Crawlers;

use App\Common\MessageBus\Messages\MessageInterface;
use App\Common\ValueObjects\Url;
use MongoDB\BSON\ObjectId;

class NewWebsiteToCrawlMessage implements MessageInterface
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
