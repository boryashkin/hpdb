<?php

namespace App\Common\MessageBus\Messages\Processors;

use App\Common\MessageBus\Messages\MessageInterface;
use App\Common\ValueObjects\Url;
use MongoDB\BSON\ObjectId;

class XmlRssContentToProcessMessage implements MessageInterface
{
    private $websiteId;
    private $content;
    private $url;

    public function __construct(ObjectId $websiteId, string $content, Url $url)
    {
        $this->websiteId = $websiteId;
        $this->content = $content;
        $this->url = $url;
    }

    public function getWebsiteId(): ObjectId
    {
        return $this->websiteId;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getUrl(): Url
    {
        return $this->url;
    }
}
