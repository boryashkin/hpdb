<?php

namespace app\messageBus\messages\processors;

use app\messageBus\messages\MessageInterface;
use MongoDB\BSON\ObjectId;

class XmlRssContentToProcessMessage implements MessageInterface
{
    private $websiteId;
    private $content;

    public function __construct(ObjectId $websiteId, string $content)
    {
        $this->websiteId = $websiteId;
        $this->content = $content;
    }

    public function getWebsiteId(): ObjectId
    {
        return $this->websiteId;
    }

    public function getContent(): string
    {
        return $this->content;
    }
}
