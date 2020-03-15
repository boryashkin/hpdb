<?php

namespace App\Common\MessageBus\Messages\Processors;

use App\Common\MessageBus\Messages\MessageInterface;
use MongoDB\BSON\ObjectId;

class XmlAtomContentToProcessMessage implements MessageInterface
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
